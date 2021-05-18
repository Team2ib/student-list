<html>
    <head>
        <title>TEAM 2</title>
	<link rel="stylesheet" href="style.css" />
    </head>

    <body>
        <h1>Student Checking App</h1>
        <ul class="center-text">
            <form action="" method="POST">
            <!--<label>Enter student name:</label><br />
            <input type="text" name="" placeholder="Student Name" required/>
            <br /><br />-->
            <button id="phpbutton" class="bouncy" type="submit" name="open">Toggle List Student</button>
            </form>

            <?php
              if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['open']))
              {
              $username = getenv('USERNAME');
              $password = getenv('PASSWORD');
              if ( empty($username) ) $username = 'fake_username';
              if ( empty($password) ) $password = 'fake_password';
              $context = stream_context_create(array(
                "http" => array(
                "header" => "Authorization: Basic " . base64_encode("$username:$password"),
              )));

              $url = 'http://student_list:5000/pozos/api/v1.0/get_student_ages';
              $list = json_decode(file_get_contents($url, false, $context), true);
              echo "<div id='api'><h2>This is the list of students with their ages</h2>";
              foreach($list["student_ages"] as $key => $value) {
                  echo "<li>- $key is $value years old -</li>";
              }
			  echo "</div>";
             }
            ?>
        </ul>
		<script type="text/javascript" src="script.js"></script>	
    </body>
</html>
