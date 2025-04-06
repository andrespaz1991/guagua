<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Registros</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Lista de Registros</h2>
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
#         echo $sql;
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
            echo "0 resultados";
        }
       # $conn->close();
        ?>
    </table>
</body>
</html>
