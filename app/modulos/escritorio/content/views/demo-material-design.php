<h2>
    Escritorio - Cloud
</h2>
<hr>
<div class="dropdown">
    <a
        class="btn btn-primary dropdown-toggle"
        href="#"
        role="button"
        id="dropdownMenuLink"
        data-mdb-toggle="dropdown"
        aria-expanded="false"
        data-bs-toggle="tooltip" data-bs-placement="top" title="Tooltip on top"
    >
        Dropdown link
    </a>

    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        <li><a class="dropdown-item" href="#">Action</a></li>
        <li><a class="dropdown-item" href="#">Another action</a></li>
        <li><a class="dropdown-item" href="#">Something else here</a></li>
    </ul>
</div>
<div class="alert alert-success" role="alert">
    <h4 class="alert-heading">Well done!</h4>
    <p>Aww yeah, you successfully read this important alert message. This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.</p>
    <hr>
    <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
</div>
<button type="button" class="btn btn-stihl ttip">
    Tooltip on top
    <span class="ttiptext">Tool tip arriba</span>
</button>
<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="right" title="Tooltip on right">
    Tooltip on right
</button>
<button type="button" class="btn btn-pedrollo" data-toggle="tooltip" data-placement="bottom" title="Tooltip on bottom">
    Tooltip on bottom
</button>
<button type="button" class="btn btn-franklin-electric" data-toggle="tooltip" data-placement="left" title="Tooltip on left">
    Tooltip on left
</button>
<hr>
<table class="table table-hover">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col"><b>First</b></th>
            <th scope="col">Last</th>
            <th scope="col">Handle</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="row">1</th>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
        </tr>
        <tr>
            <th scope="row">2</th>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
        </tr>
        <tr>
            <th scope="row">3</th>
            <td colspan="2">Larry the Bird</td>
            <td>@twitter</td>
        </tr>
    </tbody>
</table>
        
<!-- Pills navs -->
<ul class="nav nav-pills nav-justified mb-3" id="ex1" role="tablist">
    <li class="nav-item" role="presentation">
        <a
            class="nav-link"
            id="tab-login"
            data-mdb-toggle="pill"
            href="#pills-login"
            role="tab"
            aria-controls="pills-login"
            aria-selected="true"
        >
            <span class="step-form">1</span> Login
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a
            class="nav-link active"
            id="tab-register"
            data-mdb-toggle="pill"
            href="#pills-register"
            role="tab"
            aria-controls="pills-register"
            aria-selected="false"
        >
            Register
        </a>
    </li>
</ul>
<!-- Pills navs -->

<!-- Pills content -->
<div class="tab-content">
    <div
        class="tab-pane fade show active"
        id="pills-login"
        role="tabpanel"
        aria-labelledby="tab-login"
    >
        <form id="frmLogin">
            <div class="text-center mb-3">
                <p>Sign in with:</p>
                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-facebook-f"></i>
                </button>

                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-google"></i>
                </button>

                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-twitter"></i>
                </button>

                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-github"></i>
                </button>
            </div>

            <p class="text-center">or:</p>

            <div class="form-outline mb-4">
                <input
                    type="text"
                    id="form1"
                    class="form-control"
                    data-mdb-showcounter="true"
                    maxlength="20"
                    value="prueba xd"
                />
                <label class="form-label" for="form1">Limitar caracteres</label>
                <div class="form-helper"></div>
            </div>

            <!-- Email input -->
            <div class="form-outline mb-4">
                <i class="fas fa-user trailing"></i>
                <input type="email" id="loginName" class="form-control form-icon-trailing" />
                <label class="form-label active" for="loginName">Email or username</label>
            </div>

            <!-- Password input -->
            <div class="form-outline mb-4">
                <i class="fas fa-exclamation-circle trailing"></i>
                <input type="password" id="loginPassword" class="form-control form-icon-trailing" />
                <label class="form-label" for="loginPassword">Password</label>
            </div>

            <!-- maska input -->
            <div class="form-outline mb-4">
                <i class="fas fa-exclamation-circle trailing"></i>
                <input type="text" id="maskaInput" class="form-control form-icon-trailing masked input" data-mask='####-## (##)' />
                <label class="form-label" for="maskaInput">Input con maska-js</label>
            </div>

            <!-- 2 column grid layout -->
            <div class="row mb-4">
                <div class="col-md-6 d-flex justify-content-center">
                    <!-- Checkbox -->
                    <div class="form-check mb-3 mb-md-0">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            value=""
                            id="loginCheck"
                            checked
                        />
                            <label class="form-check-label" for="loginCheck"> Remember me </label>
                    </div>
                </div>

                <div class="col-md-6 d-flex justify-content-center">
                    <!-- Simple link -->
                    <a href="#!">Forgot password?</a>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary btn-block mb-4">Sign in</button>

            <!-- Register buttons -->
            <div class="text-center">
                <p>Not a member? <a href="#!">Register</a></p>
            </div>
        </form>
    </div>
    <div
        class="tab-pane fade"
        id="pills-register"
        role="tabpanel"
        aria-labelledby="tab-register"
    >
        <form id="test">
            <div class="text-center mb-3">
                <p>Sign up with:</p>
                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-facebook-f"></i>
                </button>

                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-google"></i>
                </button>

                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-twitter"></i>
                </button>

                <button type="button" class="btn btn-primary btn-floating mx-1">
                    <i class="fab fa-github"></i>
                </button>
            </div>

            <p class="text-center">or:</p>

            <!-- Name input -->
            <div class="form-outline mb-4">
                <input type="text" id="registerName" class="form-control" name="registerName" required />
                <label class="form-label" for="registerName">Name</label>
            </div>
            <div class="form-outline mb-4">
                <select class="form-select" aria-label="Default select example" required>
                  <option selected disabled>Open this select menu</option>
                  <option value="1">One</option>
                  <option value="2">Two</option>
                  <option value="3">Three</option>
                </select>
                <label class="form-label" for="registerName">Name</label>


            <!-- Username input -->
            <div class="form-outline mb-4">
                <input type="text" id="registerUsername" class="form-control" name="registerUsername" required />
                <label class="form-label" for="registerUsername">Username</label>
            </div>

            <!-- Email input -->
            <div class="form-outline mb-4">
                <input type="email" id="registerEmail" class="form-control" required />
                <label class="form-label" for="registerEmail">Email</label>
            </div>

            <!-- Password input -->
            <div class="form-outline mb-4">
                <input type="password" id="registerPassword" class="form-control" />
                <label class="form-label" for="registerPassword">Password</label>
            </div>

            <!-- Repeat Password input -->
            <div class="form-outline mb-4">
                <input type="password" id="registerRepeatPassword" class="form-control" />
                <label class="form-label" for="registerRepeatPassword">Repeat password</label>
            </div>

            <!-- Checkbox -->
            <div class="form-check d-flex justify-content-center mb-4">
                <input
                    class="form-check-input me-2"
                    type="checkbox"
                    value=""
                    id="registerCheck"
                    checked
                    aria-describedby="registerCheckHelpText"
                />
                    <label class="form-check-label" for="registerCheck">
                        I have read and agree to the terms
                    </label>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary btn-block mb-3">Sign in</button>
        </form>
    </div>
</div>
            
<!-- Button trigger modal -->
<button
    type="button"
    class="btn btn-primary"
    data-mdb-toggle="modal"
    data-mdb-target="#exampleModal"
>
    Launch demo modal
</button>

<!-- Modal -->
<div
    class="modal fade"
    id="exampleModal"
    tabindex="-1"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
            <button
              type="button"
              class="btn-close"
              data-mdb-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">...</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">
              Close
            </button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div>
        </div>
    </div>
</div>
<script>
    //window.onload = mobileControl(saludar);

    function saludar() {
        toastr["success"]("Mensaje de prueba", "Estamos en movil");
    }

    // vanilla default
    Maska.create('#frmLogin .masked');
    
    
    $("#test").validate({
        
    });
</script>