/**
 * Created by nicholaslck on 20/9/2016.
 */
$(".nav-left #notebook").click(function(){
    $("#notebooks-list").css("display","block");
    $("#phones-list").css("display","none");
});

$(".nav-left #phone").click(function(){
    $("#notebooks-list").css("display","none");
    $("#phones-list").css("display","block");
});

