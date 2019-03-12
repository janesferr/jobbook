<?php

session_start();

$_ENV['SLIM_MODE'] = 'production';
$_ENV['SLIM_MODE'] = 'development';

require 'vendor/RedBean/rb.php';
require 'vendor/autoload.php';
require 'app/includes/config.php';
require 'app/resources/Auth/auth.php';

R::setup(
    sprintf('mysql:host=%s;dbname=%s', HOST, APP_JOBBOOK_DB_DATABASE),
    APP_JOBBOOK_DB_USER,
    APP_JOBBOOK_DB_PASSWORD
);

R::freeze(true);

$auth = new \Auth\Auth();

$auth->configure(
    HOST,
    APP_LOGIN_DB_DATABASE,
    APP_LOGIN_DB_USER,
    APP_LOGIN_DB_PASSWORD
);

$app = new \Slim\Slim();

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => false,
        'debug' => true
    ));
});

$app->config(array(
	'templates.path' => 'app/templates'
));

$roleMap = array(
	ROLE_ADMINISTRATOR => 0,
	ROLE_USER => 1,
	ROLE_UNAUTH => 100);

$authorizeForRole = function($role = ROLE_USER) use ($app, $auth, $roleMap) {
	return function () use ($role, $app, $auth, $roleMap) {
		$currentRole = $auth->getUserSessionRole();
		
		if ($roleMap[$currentRole] > $roleMap[$role]) {
			$errorMessage = 'User is not authorized for this operation';
			$errorData = array('error' => $errorMessage);
			
			$app->contentType('application/json');
			$app->response()->header('X-Status-Reason', $errorMessage);
			$app->response()->status(403);
			echo json_encode($errorData);
			$app->stop();
		}
	};
};

// (ROUTES) Application pages
$app->get('/', function () use ($app, $auth) {
    if ($auth->validateSession()) {
	    $app->render('home.php', array('title' => 'Home'));
    } else {
        $app->redirect('login');
    }
});

$app->get('/login', function() use ($app, $auth){
    if ($auth->validateSession()) {
        $app->redirect('.');
    } else {
        $app->render('login.php', array('title' => 'Login to the Job Book application'));
    }
});

$app->post('/login', function() use ($app, $auth){

    $username = $app->request->post('username');
    $password = $app->request->post('password');

    $errors = array();

    if ($username == null) {
        $errors['username'] = 'Username was not provided';
    }

    if ($password == null) {
        $errors['password'] = 'Password was not provided';
    }

    if (count($errors) > 0) {
        $app->flash('loginerr', "Invalid username/password provided.");
        $app->redirect('login');
    }

    if ($auth->login($username, $password)) {
        $app->redirect('.');
    } else {
        $app->flash('loginerr', "Invalid username/password provided.");
        $app->redirect('login');
    }
});

// Logs user out of the application
$app->get('/logout', function() use ($app){
    session_destroy();
    $app->redirect('login');
});

// (ROUTES) REST API for jobs and users

// Get currently logged in user's role
$app->get('/user/role', function() use($app, $auth){
    $role = $auth->getUserSessionRole();
    $app->contentType('application/json');
    echo json_encode(array("role" => $role));
});

// Lists all jobs
$app->get('/jobs', $authorizeForRole(), function () use ($app) {
	$jobs = R::find('jobs');
	$app->contentType('application/json');
	echo json_encode(R::exportAll($jobs));
});

// Lists a specific job
$app->get('/jobs/:jobNumber', $authorizeForRole(), function ($jobNumber) use ($app) {
	try {
		$job = R::findOne('jobs', 'job_number=?', array($jobNumber));
		
		if($job) {
			$app->contentType('application/json');
			echo json_encode(R::exportAll($job)[0]);
		} else {
			throw new ResourceNotFoundException();
		}
	} catch (ResourceNotFoundException $e) {
		$app->response()->status(404);
	} catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
	}	
});

// Creates a new job
$app->post('/jobs', $authorizeForRole(), function () use ($app) {
	R::begin();
    try {
		$req = $app->request();
		$body = $req->getBody();
		$input = json_decode($body);

        $newJobNumber = property_exists($input, 'job_number') ?
            $input->job_number :
            R::getCell("SELECT COALESCE((SELECT MAX(job_number) FROM jobs) ,-1) + 1 AS 'job_number'");

		$job = R::dispense('jobs');
        $job->job_number = (string)$newJobNumber;
		$job->date = (string)$input->date;
        $job->client_name = (string)$input->client_name;

        if (property_exists($input, 'description')) {
		    $job->description = (string)$input->description;
        }
        if (property_exists($input, 'initials')) {
            $job->initials = (string)$input->initials;
        }
        if (property_exists($input, 'invoice_date')) {
            $job->invoice_date = (string)$input->invoice_date;
        }
        if (property_exists($input, 'p_number')) {
            $job->p_number = (string)$input->p_number;
        }

		$id = R::store($job);
		R::commit();
		$app->contentType('application/json');
		echo json_encode(R::exportAll($job)[0]);
	} catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
        R::rollback();
	}
});

// Updates an existing job
$app->post('/jobs/:jobNumber', $authorizeForRole(), function ($jobNumber) use ($app) {
    R::begin();
    try {
		$req = $app->request();
		$body = $req->getBody();
		$input = json_decode($body); 

		$job = R::findOne('jobs', 'job_number=?', array($jobNumber));
		
		if ($job) {
            $job->job_number = (string)$input->job_number;
			$job->date = (string)$input->date;
			$job->client_name = (string)$input->client_name;
			$job->description = property_exists($input, 'description') ? (string)$input->description : "";
			$job->initials = property_exists($input, 'initials') ? (string)$input->initials : "";
			$job->invoice_date = property_exists($input, 'invoice_date') ? (string)$input->invoice_date : "";
			$job->p_number = property_exists($input, 'p_number') ? (string)$input->p_number : "";

			R::store($job);
			R::commit();

			$app->contentType('application/json');
			echo json_encode(R::exportAll($job)[0]);
		} else {
			throw new ResourceNotFoundException();
		}
	} catch (ResourceNotFoundException $e) {
		$app->response()->status(404);
	} catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
        R::rollback();
	}
});

// Deletes a specific job
$app->delete('/jobs/:jobNumber', $authorizeForRole(ROLE_ADMINISTRATOR), function ($jobNumber) use ($app) {
    R::begin();
    try {
		$req = $app->request();
		$body = $req->getBody();
		$input = json_decode($body); 
		
		$job = R::findOne('jobs', 'job_number=?', array($jobNumber));
		
		if ($job) {
			R::trash($job);
            R::commit();

			$app->response->status(204);
			echo json_encode(R::exportAll($job)[0]);
		} else {
			throw new ResourceNotFoundException();
		}
	} catch (ResourceNotFoundException $e) {
		$app->response()->status(404);
	} catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
        R::rollback();
	}
});

$app->run();
?>