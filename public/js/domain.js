// var ruta_global = `${window.location.origin}`+'/';
function cambiar_estado_boton(id, texto, deshabilitado){
    $("#" + id).html(texto);
    $("#" + id).attr("disabled", deshabilitado);
}
function validar_campo_vacio(campo, valor, estado) {
    var objeto = document.getElementById(campo);
    if(!valor){
        respuesta('El siguiente Campo Resaltado no puede estar vacío', 'error');
        estado = false;
        objeto.classList.add('is-invalid'); // Agregar clase is-invalid si el campo está vacío
        console.log('Campo vacio: ' + campo + " Valor: " + valor);
    } else {
        objeto.style.border = '';
        objeto.classList.remove('is-invalid'); // Remover clase is-invalid si el campo está lleno
        objeto.classList.add('is-valid'); // Agregar clase is-valid si el campo está lleno y es válido
    }
    return estado;
}
function respuesta(mensaje, tipo,tiempo = 3000){
    /*const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: tiempo,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    Toast.fire({icon: tipo, title: mensaje});*/
    const options = {
        text: mensaje,
        title: tipo == 'error' ? 'Hubo un error en la operación.' :"Operación Exitosa",
        icon: tipo,
        button: false,
        timer: tiempo
    };

    // Mostrar la alerta
    swal(options);
}
function limpiarCampos(id) {
    const formulario = document.getElementById(id);
    const elementos = formulario.elements;

    for (let i = 0; i < elementos.length; i++) {
        const elemento = elementos[i];
        const tipo = elemento.type.toLowerCase();

        // Restablecer el valor según el tipo de elemento
        switch (tipo) {
            case "text":
            case "date":
            case "email":
            case "password":
            case "number":
            case "textarea":
            case "select-one":
                elemento.value = "";
                break;
            case "radio":
            case "checkbox":
                elemento.checked = false;
                break;
            // Agrega aquí más casos si tienes otros tipos de elementos
        }
        // Eliminar la clase is-valid
        if (elemento.classList.contains('is-valid')) {
            elemento.classList.remove('is-valid');
        }
        if (elemento.classList.contains('is-invalid')) {
            elemento.classList.remove('is-invalid');
        }
    }
}
function validar_numeros(id) {
    var text = document.getElementById(id).value;
    var expreg = new RegExp(/^[0-9]*$/);
    if(expreg.test(text)){
        return true;
    } else {
        //alertify.error("Carácter Inválido");
        //var long = text.length;
        //var text_to_extract = long - 1;
        //document.getElementById(id).value = text.substring(0, text_to_extract);
        var re = /[a-zA-ZñáéíóúÁÉÍÓÚ´.*+?^$&!¡¿#%/{}()='|[\]\\"]/g;
        document.getElementById(id).value = text.replace(re, '');
        return false;
    }
}
function previewImage(input, preview) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#'+preview).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
function preguntar(mensaje, funcion_usar, confirmar, denegar, id, id2 = '', id3 = ''){
    /* Swal.fire({
         title: mensaje,
         showDenyButton: true,
         showCancelButton: false,
         confirmButtonText: confirmar,
         denyButtonText: denegar,
     }).then((result) => {
         /!* Read more about isConfirmed, isDenied below *!/
         if (result.isConfirmed) {
             //Swal.fire('Saved!', '', 'success')
             if(id3 !== ''){
                 window[funcion_usar].apply(this, [id,id2,id3]);
             } else {
                 if(id2 !== ''){
                     window[funcion_usar].apply(this, [id,id2]);
                 } else {
                     window[funcion_usar].apply(this, [id]);
                 }
             }
         } else if (result.isDenied) {
             respuesta('Operacion Cancelada', 'error');
         }
     })*/
    swal({
        title: mensaje,
        icon: "warning",
        buttons: {
            cancel: denegar,
            confirm: confirmar
        },
    }).then((value) => {
        if (value) {
            if (id3 !== '') {
                window[funcion_usar](id, id2, id3);
            } else if (id2 !== '') {
                window[funcion_usar](id, id2);
            } else {
                window[funcion_usar](id);
            }
        } else {
            swal('Operacion Cancelada', '', 'error');
        }
    });
}
function mayuscula(id) {
    var texto = document.getElementById(id).value;
    document.getElementById(id).value = texto.toUpperCase();
}

document.addEventListener('DOMContentLoaded', function() {
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');

    togglePasswordIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const inputField = this.previousElementSibling; // Campo de input que precede al span con el ícono
            const iconElement = this.querySelector('i'); // El ícono dentro del span

            // Alternar entre mostrar y ocultar contraseña
            if (inputField.type === 'password') {
                inputField.type = 'text';
                iconElement.classList.remove('fa-eye');
                iconElement.classList.add('fa-eye-slash');
            } else {
                inputField.type = 'password';
                iconElement.classList.remove('fa-eye-slash');
                iconElement.classList.add('fa-eye');
            }
        });
    });
});
