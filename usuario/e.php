<!doctype html>
<html>
<head>
    <title>Zebra_Pagination, database example</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="zebra_pagination.css" type="text/css">
</head>
<body>
    <h2>Zebra_Pagination, database example</h2>
    <?php
    require 'Zebra_Pagination/Zebra_Pagination.php';
    $records_per_page = 10;
    $pagination = new Zebra_Pagination();
    $pagination->records_per_page($records_per_page);
    require 'conexion.php';
    $MySQL = ' SELECT SQL_CALC_FOUND_ROWS country  FROM   countries  ORDER BY  country  LIMIT ' . (($pagination->get_page() - 1) * $records_per_page) . ', ' . $records_per_page . '';
$consulta=$mysqli->query($MySQL);
$sql2="SELECT SQL_CALC_FOUND_ROWS country  FROM   countries  ";
$consulta2=$mysqli->query($sql2);
$pagination->records($consulta2->num_rows);
?>
    <table class="countries" border="1">
        <thead>
            <tr><th>Country</th></tr>
        </thead>
        <tbody>
        <?php while ($row = $consulta->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $row['country']; ?></td>
        </tr>
        <?php }; ?>
        </tbody>
    </table>
    <div class="text-center">
    <?php    $pagination->render();    ?>
    </div>
</body>
</html>
