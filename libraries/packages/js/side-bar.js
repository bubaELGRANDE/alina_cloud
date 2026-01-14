$(".sidebar-dropdown > a").click(function () {
    if($(this).parent().hasClass("active")) {
        $(this).parent().removeClass("active");
        $(this).next(".sidebar-submenu").slideUp(200);
    } else {
        $(this).next(".sidebar-submenu").slideDown(200);
        $(this).parent().addClass("active");
    }
});

$(".sidebar-sub-dropdown > a").click(function () {
  	if($(this).parent().hasClass("active")) {
        $(this).parent().removeClass("active");
        $(this).next(".sidebar-sub-submenu").slideUp(200);
    } else {
        $(this).next(".sidebar-sub-submenu").slideDown(200);
        $(this).parent().addClass("active");
    }
});

$(".sidebar-menu .sidebar-item a").click(function () {
    let flgSubMenu = false;
    let flgSubSubMenu = false;

    $(this).parents().map(function() {
        if(this.className == "sidebar-submenu" || this.className == "sidebar-sub-submenu") {
            if(this.className == "sidebar-sub-submenu") {
                flgSubSubMenu = true;
            } else {
                flgSubMenu = true;
            }
        } else {
        }
    });
    if($(this).parent().hasClass("active")) {
        // no remover, mantener el efecto
    } else { // class active al modulo
        $(".sidebar-item").removeClass("active");
        $(this).parent().addClass("active");
    }

    if(flgSubMenu == false) { // 1er nivel menu
        $(".sidebar-dropdown").removeClass("active");
        $(".sidebar-sub-dropdown").removeClass("active");
        $(".sidebar-sub-submenu").slideUp(200);
        $(".sidebar-submenu").slideUp(200);
    } else {
        if(flgSubSubMenu == true) { // 3er nivel menu
            $(".sidebar-dropdown").not($(this).parent().parent().parent().parent().parent().parent().parent()).removeClass("active");
            $('.sidebar-submenu').not($(this).parent().parent().parent().parent().parent().parent()).slideUp(200);
            $(".sidebar-sub-submenu").not($(this).parent().parent().parent()).slideUp(200);
            $(".sidebar-sub-dropdown").not($(this).parent().parent().parent().parent()).removeClass("active");
        } else { // 2do nivel menu
            $(".sidebar-dropdown").not($(this).parent().parent().parent().parent()).removeClass("active");
            $('.sidebar-submenu').not($(this).parent().parent().parent()).slideUp(200);
            $(".sidebar-sub-dropdown").removeClass("active");
            $(".sidebar-sub-submenu").slideUp(200);
        }
    }
});

$("#sidebar").hover(function () {
    $(".page-wrapper").addClass("sidebar-hovered");
}, function () {
    $(".page-wrapper").removeClass("sidebar-hovered");
});

$('#toggle-sidebar').click(function (e) {
    $('.page-wrapper').removeClass('pinned');
    $('#sidebar').unbind('hover');
    $('.page-wrapper').toggleClass('toggled');
    $("#menuShow").toggle();
    $("#menuHide").toggle();
    $("#pinExpand").hide();
    $("#pinCompress").show();

    e.preventDefault();
    e.stopImmediatePropagation();
});

//Pin sidebar
$('#pin-sidebar').click(function (e) {
    if($('.page-wrapper').hasClass('pinned')) {
        // unpin sidebar when hovered
        $('.page-wrapper').removeClass('pinned');
        $('#sidebar').unbind('hover');
        $("#pinCompress").toggle("show");
        $("#pinExpand").toggle("hide");
    } else {
        if($('.page-wrapper').hasClass('toggled')) {
            $("#pinCompress").toggle("hide");
            $("#pinExpand").toggle("show");
        } else {
            $("#menuShow").toggle("hide");
            $("#menuHide").toggle("show");
            $("#pinCompress").hide();
            $("#pinExpand").show();
        }
        $('.page-wrapper').addClass('pinned');
        $('.page-wrapper').addClass('toggled');
        $('#sidebar').hover(function () {
            $('.page-wrapper').addClass('sidebar-hovered');
        }, function () {
            $('.page-wrapper').removeClass('sidebar-hovered');
        });
    }
    e.preventDefault();
    e.stopImmediatePropagation();
});

// modo darks  
const theme = document.querySelector("#theme-link");

function asyncTheme() {
    var btn = $(this).find('.drk-toggle');
    var tema;
    if(theme.getAttribute("href") == "../libraries/packages/css/styles.css") {
        tema = "dark";
    } else {
        tema = "default";
    }
    let data = {tema: tema};
    asyncDoDataReturn('../libraries/includes/logic/themes/themeChange/', data, function(data) {
       if (theme.getAttribute("href") == "../libraries/packages/css/styles.css") {
           theme.href = "../libraries/packages/css/styles-dark.css";
           $(".drk-toggle").html('Modo claro <span class="badge rounded-pill bg-info text-dark"><i class="fas fa-sun fa-lg"></i></span>');
        } else {
            theme.href = "../libraries/packages/css/styles.css";
            $(".drk-toggle").html('Modo oscuro <span class="badge rounded-pill bg-dark"><i class="fas fa-moon fa-lg"></i></span>');
        }
    });
}