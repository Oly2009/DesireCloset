<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DesireCloset</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- CONTENIDO -->
<div class="index container-fluid d-flex align-items-center justify-content-center min-vh-100 ">
    <div class="row justify-content-center w-auto ">
        <div class="col-lg-12 col-md-10 col-sm-12 ">
            <div class="card custom-card bg-black">
                <div class="card-body">
                    <h1 class="card-title text-danger">¿Eres mayor de edad?</h1>
                     <form id="ageForm" action="vista/principal.php" method="POST">
                        <div class="row mb-3 text-danger">
                            <h3> Introduce tu fecha de nacimiento: </h3><br>
                            <div class="col">
                                <label for="fecha_dia" class="form-label text-danger">Día:</label>
                                <input type="number" class="form-control" id="fecha_dia" name="fecha_dia" min="1" max="31" required>
                            </div>
                            <div class="col">
                                <label for="fecha_mes" class="form-label text-danger">Mes:</label>
                                <input type="number" class="form-control" id="fecha_mes" name="fecha_mes" min="1" max="12" required>
                            </div>
                            <div class="col">
                                <label for="fecha_anio" class="form-label text-danger">Año:</label>
                                <input type="number" class="form-control" id="fecha_anio" name="fecha_anio" min="1900" max="<?php echo date('Y');?>" required>
                            </div>
                        </div>
                        <button type="button" onclick="validarEdad()" class="btn btn-danger">Comprobar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function validarEdad() {
        var dia = parseInt(document.getElementById("fecha_dia").value);
        var mes = parseInt(document.getElementById("fecha_mes").value);
        var anio = parseInt(document.getElementById("fecha_anio").value);

        // Verificar si se han ingresado los valores de día, mes y año
        if (isNaN(dia) || isNaN(mes) || isNaN(anio)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Por favor, ingrese su fecha de nacimiento'
            });
            return; // Detener la ejecución de la función si falta alguna fecha
        }

        // Verificar si la fecha de nacimiento está dentro del rango permitido
        if (anio < 1900 || anio > new Date().getFullYear()) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'La fecha de nacimiento no está comprendida en el rango permitido.\n\
       Rango permitido 1900-actualidad'
            });
            return; // Detener la ejecución de la función si la fecha está fuera del rango
        }

        var fechaNacimiento = new Date(anio, mes - 1, dia);
        var fechaHoy = new Date();
        
        // Comprobar si la fecha de nacimiento es después de la fecha de hoy
        if (fechaNacimiento > fechaHoy) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'La fecha de nacimiento no puede ser después de la fecha actual'
            });
            return;
        }

        // Calcular la edad
        var edad = fechaHoy.getFullYear() - fechaNacimiento.getFullYear();
        var mesCumple = fechaNacimiento.getMonth() + 1;
        var diaCumple = fechaNacimiento.getDate();
        
        // Verificar si ya ha pasado el cumpleaños este año
        if (mesCumple > fechaHoy.getMonth() + 1 || (mesCumple === fechaHoy.getMonth() + 1 && diaCumple > fechaHoy.getDate())) {
            edad--;
        }
        
        // Verificar si es mayor de 18 años
        if (edad < 18) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Debes ser mayor de 18 años para acceder a la página'
            });
        } else {
            // Si es mayor de 18 años, enviar el formulario
            document.getElementById("ageForm").submit();
        }
    }
</script>
</body>
</html>
