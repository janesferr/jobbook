<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $title ?></title>
	
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	<link href="app/css/login.css" rel="stylesheet">
</head>
<body>
    <!-- Start Navigation -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <img src="app/images/logo.jpeg" width="50%" height="100%">
                <a class="navbar-brand" href="#">Job Book</a>
            </div>
        </div>
    </div>
    <!-- End Navigation -->
	<div class="container">
        <div class="jumbotron">
            <form class="form-login" method="post">
                <h2 class="form-login-heading">Job Book Login</h2>
                <h3>Please log in to continue</h3>
                <input type="text" name="username" class="form-control" placeholder="Username"
                       autocapitalize="off" required autofocus>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>
                <p><?= isset($flash['loginerr']) ? $flash['loginerr'] : '' ?></p>
            </form>
        </div>
	</div>
</body>
</html>