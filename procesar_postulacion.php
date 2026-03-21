<?php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$pdo = get_db_connection();

$vacante_id = (int)$_POST['vacante_id'];
$nombre_completo = clean_input($_POST['nombre_completo']);
$dni = clean_input($_POST['dni']);
$correo_estudiante = clean_input($_POST['correo_estudiante']);
$celular = clean_input($_POST['celular']);

// Handle File Upload
$target_dir = "uploads/cvs/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$filename = time() . "_" . basename($_FILES["cv_pdf"]["name"]);
$target_file = $target_dir . $filename;
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if($fileType != "pdf") {
    redirect_with_message("vacante_detalle.php?id=$vacante_id", "Solo se admiten archivos PDF.", "error");
}

if (!str_ends_with($correo_estudiante, '.edu.pe')) {
    redirect_with_message("vacante_detalle.php?id=$vacante_id", "El correo de contacto debe tener el dominio .edu.pe (Ej: estudiante@unfv.edu.pe)", "error");
}

if (move_uploaded_file($_FILES["cv_pdf"]["tmp_name"], $target_file)) {
    $url_cv_pdf = $target_file;
    
    // IA SIMULATION / MATCH
    // In a real scenario, this would involve parsing the PDF and comparing it with the vacancy description.
    $match_porcentaje = rand(50, 98) + (rand(0, 99) / 100);
    $ia_analisis_descripcion = "Análisis IA: El perfil del candidato coincide con las palabras clave del puesto. Se recomienda para entrevista.";

    try {
        $stmt = $pdo->prepare("INSERT INTO postulaciones (vacante_id, dni, nombre_completo, correo_estudiante, celular, url_cv_pdf, match_porcentaje, ia_analisis_descripcion) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$vacante_id, $dni, $nombre_completo, $correo_estudiante, $celular, $url_cv_pdf, $match_porcentaje, $ia_analisis_descripcion]);
        
        redirect_with_message("index.php", "¡Postulación enviada con éxito! La empresa revisará tu perfil.", "success");
    } catch (PDOException $e) {
        redirect_with_message("vacante_detalle.php?id=$vacante_id", "Error al procesar la postulación: " . $e->getMessage(), "error");
    }

} else {
    redirect_with_message("vacante_detalle.php?id=$vacante_id", "Error al subir el archivo CV.", "error");
}
?>
