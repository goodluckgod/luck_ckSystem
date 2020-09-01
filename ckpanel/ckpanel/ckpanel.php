<?php
session_start();


// Include config file
require_once "config.php";


$hex = "";
$hex_err = "";
$username = $password = "";
$username_err = $password_err = "";


// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if hex is empty
    if(empty(trim($_POST["hex"]))){
        $hex_err = "Kişinin HexID'sini gir.";
    } else{
        $hex = trim($_POST["hex"]);
    }

       // Check if username is empty
       if(empty(trim($_POST["username"]))){
        $username_err = "Kullanıcı adını gir.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Şifreni gir.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($hex_err) && empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users_ckpanel WHERE username = ?";
        $sql2 = "SELECT identifier FROM users WHERE identifier = ?";
        

        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if($password == $hashed_password){
                            if($stmt2 = mysqli_prepare($link, $sql2)){
                                // Bind variables to the prepared statement as parameters
                                mysqli_stmt_bind_param($stmt2, "s", $param_hex);
                                
                                // Set parameters
                                $param_hex = $hex;
                                
                                // Attempt to execute the prepared statement
                                if(mysqli_stmt_execute($stmt2)){
                                    // Store result
                                    mysqli_stmt_store_result($stmt2);
                                    
                                    // Check if hex exists, if yes then verify password
                                    if(mysqli_stmt_num_rows($stmt2) == 1){                    
                                        // Bind result variables
                                        mysqli_stmt_bind_result($stmt2, $hex);
                                        if(mysqli_stmt_fetch($stmt2)){
                                            
                                            $sql = "DELETE FROM users WHERE identifier='" . $hex . "'";
                                            if(mysqli_query($link, $sql)){
                                                $hex_err =  "Başarıyla CK atıldı!";
                                               
                                            } else{
                                                $hex_err =  "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                                            }
                                        }
                                    } else{
                                        // Display an error message if hex doesn't exist
                                        $hex_err = "Böyle bir HexID yok!";
                                    }
                                } else{
                                    $hex_err =  "Bir şeyler test gitti, goodluck ile temasa geçin.";
                                }
                    
                                // Close statement
                                mysqli_stmt_close($stmt);
                            }
                        } else{
                            // Display an error message if password is not valid
                            $password_err = "Girdiğiniz şifre doğru değil.";
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "Böyle bir kullanıcaya ait bir hesap bulunamadı.";
                }
            } else{
                echo "Bir şeyler test gitti, goodluck ile temasa geçin.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
       
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>goodluck CKSystem</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 400px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>goodluck CKSystem</h2>
        
        <p>Kişinin HexID'sini girin (Örnek: steam:110000140fd5185)</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Kullancı Adı</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Şifre</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($hex_err)) ? 'has-error' : ''; ?>">
                <label>HexID</label>
                <input type="text" name="hex" class="form-control" value="<?php echo $hexid; ?>">
                <span class="help-block"><?php echo $hex_err; ?></span>
            </div>    
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="CK At">
            </div>
        </form>

    </div>    
</body>
</html>