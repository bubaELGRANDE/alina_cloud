<?php
@session_start();
if (isset($_SESSION["usuarioId"])) {
    header("Location: /app");
} else {
}
$fhActual = date("Y-m-d H:i:s");

if (!isset($_SESSION["intentosExternos"])) {
    $_SESSION["intentosExternos"] = 0;
} else {
    //$_SESSION["intentosExternos"] = 0;
}

$showCardLogin = "login";
if (isset($_REQUEST['url'])) {
    if (base64_decode($_REQUEST['url']) == "forgot-password") {
        $showCardLogin = "olvido-contrasena";
    } else {
        // Es un token para restablecer acceso
        $showCardLogin = "restablecer-acceso";
    }
} else {
    // Login normal, sin variables
}

if ($_SESSION["intentosExternos"] <= 5) {
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex">
        <title>Alina - Cloud</title>

        <!-- Bootstrap 5.3.3 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="libraries/packages/css/styles.css" />
        <link rel="icon" href="libraries/resources/images/logos/favicon.ico" type="image/ico" />
    </head>

    <body class="login" styles="background{#ddd;}">
        <div class="container">
            <div class="row row100 justify-content-md-center align-items-center">
                <div class="col-lg-4 text-center">
                    <div id="login" class="card" <?php echo ($showCardLogin == "login" ? '' : 'style="display: none;"'); ?>>
                        <div class="card-body">
                            <img class="mb-4" src="libraries/resources/images/logos/alina-logo.png" height=60>
                            <h3 class="card-title mb-4">Iniciar sesión</h3>
                            <form id="frmLogin">
                                <!-- Email input -->
                                <div class="form-outline mb-4">
                                    <i class="fas fa-envelope trailing"></i>
                                    <input type="email" id="mailLogin" class="form-control" name="mailLogin" required />
                                    <label class="form-label" for="mailLogin">Correo electrónico</label>
                                </div>
                                <div id="checkMail"></div>
                                <!-- Password input -->
                                <div class="form-outline mb-4">
                                    <i class="fas fa-lock trailing"></i>
                                    <input type="password" id="passLogin" class="form-control" name="passLogin" required />
                                    <label class="form-label" for="passLogin">Contraseña</label>
                                </div>

                                <!-- 2 column grid layout -->
                                <div class="row mb-4">
                                    <div class="col-md-6 d-flex justify-content-center">
                                        <!-- Checkbox 
                                                <div class="form-check mb-3 mb-md-0">
                                                    <input class="form-check-input" type="checkbox" value="1" id="rememberData" name="rememberData" />
                                                    <label class="form-check-label" for="rememberData"> Recordar mis datos </label>
                                                </div>
                                                -->
                                        <div id="divShowHidePass" class="form-check form-switch mb-3 mb-md-0">
                                            <input class="form-check-input" type="checkbox" id="showHidePass" />
                                            <label id="txtShowPass" class="form-check-label" for="showHidePass">Mostrar
                                                contraseña</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 d-flex justify-content-center">
                                        <!-- Simple link -->
                                        <a id="forgot" href="javascript: void(0);">¿Olvidó su contraseña?</a>
                                    </div>
                                </div>
                                <!-- Submit button -->
                                <button type="submit" id="btnLogin" class="btn btn-primary btn-lg btn-block mb-4"><i
                                        class="fas fa-sign-in-alt"></i> Iniciar sesión</button>
                                <!-- Register buttons -->
                                <div class="text-center">
                                    <p>¿No tiene una cuenta? <a id="regist" href="javascript: void(0);">Solicitar un
                                            acceso</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="registro" class="card" style="display:none;">
                        <div class="card-body">
                            <img class="mb-4" src="libraries/resources/images/logos/alina-logo.png" height=60>
                            <h3 class="card-title mb-4">Registrarse</h3>
                            <form id="frmRegist">
                                <!-- DUI input -->
                                <div class="form-outline mb-4">
                                    <i class="fas fa-address-card trailing"></i>
                                    <input type="text" id="dui" class="form-control masked" name="dui"
                                        data-mask="########-#" data-rule-minlength="10" required />
                                    <label class="form-label" for="dui">DUI</label>
                                </div>
                                <!-- Date input -->
                                <div class="form-outline mb-4 input-daterange">
                                    <i class="fa fa-calendar trailing"></i>
                                    <input type="text" id="fechaNacimiento" name="fechaNacimiento"
                                        class="form-control text-start masked" data-mask="##-##-####" required>
                                    <label class="form-label" id="start-p" for="fechaNacimiento">Fecha de nacimiento</label>
                                </div>

                                <!-- Email input -->
                                <div class="form-outline mb-4">
                                    <i class="fas fa-envelope trailing"></i>
                                    <input type="email" id="mailRegister" class="form-control" name="mailRegister"
                                        required />
                                    <label class="form-label" for="mailRegister">Correo electrónico</label>
                                </div>
                                <div id="nombreHidden" class="form-outline mb-4" style="display:none">
                                    <i class="fas fa-user-alt trailing"></i>
                                    <input type="text" id="nameRegister" class="form-control" name="nameRegister"
                                        required />
                                    <label class="form-label" for="nameRegister">Nombre completo</label>
                                </div>

                                <!-- Submit button -->
                                <button type="submit" id="btnRegister" class="btn btn-primary btn-lg btn-block mb-4"><i
                                        class="fas fa-sign-in-alt"></i> Registrarse</button>
                                <!-- Register buttons -->
                                <div class="text-center">
                                    <p>¿Ya posee una cuenta? <a id="inisec" href="javascript: void(0);">Iniciar sesión</a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="divOlvido" class="card" <?php echo ($showCardLogin == "olvido-contrasena" ? '' : 'style="display:none;"'); ?>>
                        <div class="card-body">
                            <img class="mb-4" src="libraries/resources/images/logos/alina-logo.png" height=60>
                            <h3 class="card-title mb-4">¿Olvidó su contraseña?</h3>
                            <form id="frmOlvido">
                                <!-- DUI input -->
                                <div class="form-outline mb-4">
                                    <i class="fas fa-address-card trailing"></i>
                                    <input type="text" id="duiRestablecer" class="form-control masked" name="duiRestablecer"
                                        data-mask="########-#" data-rule-minlength="10" required />
                                    <label class="form-label" for="duiRestablecer">DUI</label>
                                </div>
                                <!-- Date input -->
                                <div class="form-outline mb-4 input-daterange">
                                    <i class="fa fa-calendar trailing"></i>
                                    <input type="text" id="fechaNacimientoRestablecer" name="fechaNacimientoRestablecer"
                                        class="form-control text-start masked" data-mask="##-##-####" required>
                                    <label class="form-label" id="start-p" for="fechaNacimientoRestablecer">Fecha de
                                        nacimiento</label>
                                </div>
                                <!-- Email input -->
                                <div class="form-outline mb-4">
                                    <i class="fas fa-envelope trailing"></i>
                                    <input type="email" id="mailRestablecer" class="form-control" name="mailRestablecer"
                                        required />
                                    <label class="form-label" for="mailRestablecer">Correo electrónico</label>
                                </div>

                                <!-- Submit button -->
                                <button type="submit" id="btnRecuperar" class="btn btn-primary btn-lg btn-block mb-4"><i
                                        class="fas fa-sign-in-alt"></i> Restablecer acceso</button>
                                <!-- Register buttons -->
                                <div class="text-center">
                                    <p>¿Ya posee una cuenta? <a id="inisec2" href="javascript: void(0);">Iniciar sesión</a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    if ($showCardLogin == "restablecer-acceso") {
                        ?>
                        <div id="divRestablecer" class="card">
                            <div class="card-body">
                                <img class="mb-4" src="libraries/resources/images/logos/alina-logo.png" height=60>
                                <h3 class="card-title mb-4">Restablecer acceso</h3>
                                <form id="frmRestablecer">
                                    <input type="hidden" name="token" value="<?php echo $_REQUEST['url']; ?>">
                                    <label>Para restablecer el acceso a su cuenta, ingrese y confirme su nueva
                                        contraseña:</label>
                                    <!-- Password input -->
                                    <div class="form-outline mb-4 mt-4">
                                        <i class="fas fa-lock trailing"></i>
                                        <input type="password" id="passRestablecer" class="form-control" name="passRestablecer"
                                            required />
                                        <label class="form-label" for="passRestablecer">Nueva contraseña</label>
                                    </div>
                                    <!-- Password input -->
                                    <div class="form-outline mb-4 mt-2">
                                        <i class="fas fa-lock trailing"></i>
                                        <input type="password" id="passRestablecerConfirm" class="form-control"
                                            name="passRestablecerConfirm" required />
                                        <label class="form-label" for="passRestablecerConfirm">Confirme nueva contraseña</label>
                                    </div>
                                    <!-- 2 column grid layout -->
                                    <div class="row mb-4">
                                        <div class="col-md-12 d-flex">
                                            <div id="divShowHidePassRestablecer" class="form-check form-switch mb-3 mb-md-0">
                                                <input class="form-check-input" type="checkbox" id="showHidePassRestablecer" />
                                                <label id="txtShowPassRestablecer" class="form-check-label"
                                                    for="showHidePassRestablecer">Mostrar contraseñas</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Submit button -->
                                    <button type="submit" id="btnRestablecer" class="btn btn-primary btn-lg btn-block mb-4"><i
                                            class="fas fa-sign-in-alt"></i> Restablecer acceso</button>
                                    <!-- Register buttons -->
                                    <div class="text-center">
                                        <p>¿Ya posee una cuenta? <a id="inisec3" href="javascript: void(0);">Iniciar sesión</a>
                                        </p>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php
                    } else {
                        // No se dibuja
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>

    <!-- jQuery 3.7.1 -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <script type="text/javascript" src="libraries/packages/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="libraries/packages/js/mdb.min.js"></script>
    <script type="text/javascript" src="libraries/packages/js/swal-two.min.js"></script>
    <script type="text/javascript" src="libraries/packages/js/swal-messages.js"></script>
    <script type="text/javascript" src="libraries/packages/js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="libraries/packages/js/maska-js.min.js"></script>
    <script type="text/javascript" src="libraries/packages/js/cloud-login.js"></script>

    </html>
    <?php
} else {
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alina - Cloud</title>
        <link rel="stylesheet" href="libraries/packages/css/styles.css" />
        <link rel="icon" href="libraries/resources/images/logos/favicon.ico" type="image/ico" />
    </head>

    <body class="login" styles="background{#ddd;}">
        <div class="container">
            <div class="row row100 justify-content-md-center align-items-center">
                <div class="col-lg-4 text-center">
                    <div id="login" class="card">
                        <div class="card-body">
                            <img class="mb-4" src="libraries/resources/images/logos/alina-logo.png" height=60>
                            <h3 class="card-title mb-4">Iniciar sesión</h3>
                            <!-- Email input -->
                            <div class="form-outline mb-4">
                                <i class="fas fa-envelope trailing"></i>
                                <input type="email" class="form-control" required disabled />
                                <label class="form-label" for="mailLogin">Correo electrónico</label>
                            </div>
                            <div id="checkMail"></div>
                            <!-- Password input -->
                            <div class="form-outline mb-4">
                                <i class="fas fa-lock trailing"></i>
                                <input type="password" class="form-control" required disabled />
                                <label class="form-label" for="passLogin">Contraseña</label>
                            </div>

                            <!-- 2 column grid layout -->
                            <div class="row mb-4">
                                <div class="col-md-6 d-flex justify-content-center">
                                    <!-- Checkbox 
                                            <div class="form-check mb-3 mb-md-0">
                                                <input class="form-check-input" type="checkbox" value="1" id="rememberData" name="rememberData" />
                                                <label class="form-check-label" for="rememberData"> Recordar mis datos </label>
                                            </div>
                                            -->
                                    <div id="divShowHidePass" class="form-check form-switch mb-3 mb-md-0">
                                        <input class="form-check-input" type="checkbox" id="showHidePass" disabled />
                                        <label id="txtShowPass" class="form-check-label" for="showHidePass">Mostrar
                                            contraseña</label>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex justify-content-center">
                                    <!-- Simple link -->
                                    <a role="button">¿Olvidó su contraseña?</a>
                                </div>
                            </div>
                            <!-- Submit button -->
                            <button type="button" id="btnLogin" class="btn btn-primary btn-lg btn-block mb-4" disabled><i
                                    class="fas fa-sign-in-alt"></i> Iniciar sesión</button>
                            <!-- Register buttons -->
                            <div class="text-center">
                                <p>¿No tiene una cuenta? <a id="regist" role="button">Solicitar un acceso</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </body>

    <!-- jQuery 3.7.1 -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <script type="text/javascript" src="libraries/packages/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="libraries/packages/js/mdb.min.js"></script>

    </html>
    <?php
}
?>