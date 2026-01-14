<?php 
    @session_start();
?>
                </div>
                <br>
                <footer class="text-center">
                    <div class="mb-2">
                        <small>
                            © <?php echo date("Y"); ?> Alina Jewerly
                        </small>
                    </div>
                </footer>
            </div>
            <div id="overlayAfk" class="overlay-afk">
                <i class="fas fa-user-clock fa-5x"></i>
            </div>
        </main>
        <!-- page-content" -->
        <div id="divModal"></div>
    </div>
    <div id="AFK"></div>
    <!-- Contextmenu para funciones propias del clic derecho -->
    <div id="rightclick-menu" class="dropdown-menu" style="display: none;">
        <a class="dropdown-item" role="button" onclick="location.reload();">Actualizar (F5)</a>
        <a class="dropdown-item" role="button">Copiar (Ctrl+C)</a>
        <a class="dropdown-item" role="button">Pegar (Ctrl+P)</a>
        <hr>
        <a class="dropdown-item" role="button">Deshacer (Ctrl+Z)</a>
        <a class="dropdown-item" role="button">Rehacer (Ctrl+Shift+Z)</a>
    </div>
    <!-- page-wrapper -->
    
    <!-- jQuery 3.7.1 -->
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script type="text/javascript" src="../libraries/packages/js/swal-two.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/swal-messages.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/JcloudS.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/side-bar.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/mdb.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/maska-js.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/jquery-autocomplete.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/datatables.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/select2.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/moment.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/jquery.jOrgChart.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/jquery-smartphoto.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/slick.min.js"></script>
    <!-- Datatables -->
    <script type="text/javascript" src="../libraries/packages/js/datatables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/datatables/buttons.bootstrap4.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/datatables/jszip.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/datatables/pdfmake.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/datatables/vfs_fonts.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/datatables/buttons.html5.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/datatables/buttons.print.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/chartjs.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/table2excel.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/sheetjs.min.js"></script>
    <script type="text/javascript" src="../libraries/packages/js/signature_pad.umd.min.js"></script>

    <script>
        window.onload = startApp();
    </script>
</body>
</html>