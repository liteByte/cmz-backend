<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <title>Recupera tu contraseña!</title>
</head>
<body>
<div width='100%' style='background-color: #eee; padding: 30px 0px; padding-bottom: 0;'>

    <table border='0' cellspacing='0' cellpadding='0' width='700' align='center' style='color: #CCCCCC;'>
        <tr style='height: 70px; background-color:  #0faddb'>
            <td>
                <img style='display: block; margin: auto;' src='http://www.centromedicorondilla.com/images/logo.png' alt="cmz_logo"/>
            </td>
        </tr>
    </table>

    <table border='0' cellspacing='0' cellpadding='0' width='700' align='center' style='color: #CCCCCC;'>
        <tr style='background-color: rgb(255, 255, 255);'>
            <td style='padding-top: 10px; padding-bottom: 8px; color: black; font-size: 22px; text-align: center;'>
                Hola <strong><?php echo $name; ?></strong>,
            </td>
        </tr>
        <tr style='background-color: rgb(255, 255, 255);'>
            <td style='padding-bottom: 10px; color: black; font-weight: bold; font-size: 16px; text-align: center;'>
                ¿Olvidaste tu contraseña?
            </td>
        </tr>
    </table>

</div>
<div width='100%' style='background-color: #eee; '>

    <table border='0' cellspacing='0' cellpadding='0' width='700px' align='center' style='background-color: white; color: #CCCCCC;'>
        <tr>
            <td width='50px'></td>
            <td width='600px' style='background-color:  #0faddb; text-align:center;'>
                <p style='color: white; font-size: 16px; margin-left: 20px; margin-top: 20px;'>Tu nueva contraseña es <strong><?php echo $password; ?></strong></p>
                <p style='color: white; font-size: 16px; margin-left: 20px;'>Recordá que podés modificarla ingresando al ingresar a tu perfil</p>
            </td>
            <td width='50px'></td>
        </tr>
    </table>

    <table border='0' cellspacing='0' cellpadding='0' width='700px' align='center' style='background-color: white; color: #CCCCCC;'>
        <tr>
            <td style='padding-bottom: 10px; color: black; font-weight: bold; font-size: 20px; text-align: center;'>
                El equipo de CMZ!
            </td>
        </tr>
    </table>

</div>
</body>
</html>