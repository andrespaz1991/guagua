<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Registros</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-container {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-container label {
            margin-right: 10px;
        }
        .form-container select, .form-container input[type="submit"] {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .form-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Lista de Registros</h2>

    <div class="form-container">
        <form action="../Planeador/planeador.php" method="get" target="_blank">
            <label for="mes">Mes:</label>
            <select name="mes" id="mes">
                <option value="enero">Enero</option>
                <option value="febrero">Febrero</option>
                <option value="marzo">Marzo</option>
                <option value="abril">Abril</option>
                <option value="mayo">Mayo</option>
                <option value="junio">Junio</option>
                <option value="julio">Julio</option>
                <option value="agosto">Agosto</option>
                <option value="septiembre">Septiembre</option>
                <option value="octubre">Octubre</option>
                <option value="noviembre">Noviembre</option>
                <option value="diciembre">Diciembre</option>
            </select>

            <label for="anio">Año:</label>
            <select name="anio" id="anio">
                <?php
                $anio_actual = date("Y");
                for ($i = $anio_actual; $i >= $anio_actual - 10; $i--) {
                    echo "<option value=\"$i\">$i</option>";
                }
                ?>
            </select>

            <input type="submit" value="Generar Reporte">
        </form>
    </div>

    <table>
        <tr>
            <th>ID Plan</th>
            <th>Fecha de Creación</th>
            <th>Fecha de Inicio</th>
            <th>Fecha Fin</th>
            <th>Grado</th>
            <th>Materia</th>
            <th>DBA</th>
            <th>Descargar</th>
        </tr>
        <?php
        // Conexión a la base de datos
        require '../comun/conexion.php';
        require_once("../comun/autoload.php");
        // Consulta SQL para obtener los registros
        $sql = "SELECT * FROM planeador_vallesol inner join asignacion on asignacion.id_asignacion = planeador_vallesol.materia inner join materia_oficial on materia_oficial.id_materia=asignacion.id_asignatura order by planeador_vallesol.id_plan desc;";
        # echo $sql;
        $result = $mysqli->query($sql);

        // Mostrar los registros en la tabla
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id_plan"] . "</td>";
                echo "<td>" .Fecha::formato_fecha($row["fecha_creacion"]). "</td>";
                echo "<td>" .Fecha::formato_fecha($row["fecha_inicio"]). "</td>";
                echo "<td>" .Fecha::formato_fecha($row["fecha_fin"]). "</td>";
                echo "<td>" .($row["grado"]) . "</td>";
                echo "<td>" .$row["nombre_materia"]. "</td>";
                echo "<td>" . $row["dba"] . "</td>";
                echo "<td><a href='ejemplo.php?id=".$row["id_plan"] ."' target='_blank'>Descargar</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>0 resultados</td></tr>";
        }
        # $mysqli->close();
        ?>
    </table>
</body>
</html>
