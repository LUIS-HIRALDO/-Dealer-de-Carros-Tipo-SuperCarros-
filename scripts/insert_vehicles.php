<?php
// scripts/insert_vehicles.php
// Inserta 10 vehículos de prueba en la base de datos usando config/db.php

require_once __DIR__ . '/../config/db.php';

$vehicles = [
    [ 'año'=>2024, 'precio'=>2500000.00, 'color'=>'Rojo', 'vin'=>'VIN-TEST-0001', 'descripcion'=>'Toyota Highlander 2024, excelente estado, motor 3.5L V6, paquete premium.', 'foto'=>'/uploads/vehiculos/veh_693798ada00725.54618457.webp' ],
    [ 'año'=>2023, 'precio'=>1850000.00, 'color'=>'Blanco', 'vin'=>'VIN-TEST-0002', 'descripcion'=>'Toyota Highlander 2023, bajo kilometraje, interior en cuero.', 'foto'=>'/uploads/vehiculos/veh_69389541b432a5.45847126.jpg' ],
    [ 'año'=>2022, 'precio'=>1600000.00, 'color'=>'Negro', 'vin'=>'VIN-TEST-0003', 'descripcion'=>'Uso familiar, mantenimiento al día, excelente rendimiento.', 'foto'=>'/uploads/vehiculos/veh_6939f0d4853ae5.54933175.jpg' ],
    [ 'año'=>2021, 'precio'=>1400000.00, 'color'=>'Azul', 'vin'=>'VIN-TEST-0004', 'descripcion'=>'Vehículo de único dueño, historial limpio.', 'foto'=>'/uploads/vehiculos/veh_693798ada00725.54618457.webp' ],
    [ 'año'=>2020, 'precio'=>1200000.00, 'color'=>'Gris', 'vin'=>'VIN-TEST-0005', 'descripcion'=>'Buen estado general, ideal para ciudad y viaje.', 'foto'=>'/uploads/vehiculos/veh_69389541b432a5.45847126.jpg' ],
    [ 'año'=>2019, 'precio'=>950000.00,  'color'=>'Verde', 'vin'=>'VIN-TEST-0006', 'descripcion'=>'Ocasión, precio negociable, motor revisado.', 'foto'=>'/uploads/vehiculos/veh_6939f0d4853ae5.54933175.jpg' ],
    [ 'año'=>2018, 'precio'=>850000.00,  'color'=>'Beige', 'vin'=>'VIN-TEST-0007', 'descripcion'=>'Kilometraje moderado, mantenimiento en taller autorizado.', 'foto'=>'/uploads/vehiculos/veh_693798ada00725.54618457.webp' ],
    [ 'año'=>2017, 'precio'=>720000.00,  'color'=>'Plata', 'vin'=>'VIN-TEST-0008', 'descripcion'=>'Modelo confiable, revisión técnica al día.', 'foto'=>'/uploads/vehiculos/veh_69389541b432a5.45847126.jpg' ],
    [ 'año'=>2016, 'precio'=>650000.00,  'color'=>'Naranja', 'vin'=>'VIN-TEST-0009', 'descripcion'=>'Buen estado mecánico, ideal para quien busca ahorrar.', 'foto'=>'/uploads/vehiculos/veh_6939f0d4853ae5.54933175.jpg' ],
    [ 'año'=>2015, 'precio'=>520000.00,  'color'=>'Amarillo', 'vin'=>'VIN-TEST-0010', 'descripcion'=>'Vehículo económico, ideal para uso urbano.', 'foto'=>'/uploads/vehiculos/veh_693798ada00725.54618457.webp' ],
];

$insertSql = "INSERT INTO vehiculos (id_categoria, id_marca, id_modelo, id_estatus, id_vendedor, `año`, precio, color, vin, descripcion, foto)
VALUES (:id_categoria, :id_marca, :id_modelo, :id_estatus, :id_vendedor, :anio, :precio, :color, :vin, :descripcion, :foto)";
$checkSql = "SELECT COUNT(*) AS cnt FROM vehiculos WHERE vin = :vin";

$stmtInsert = $pdo->prepare($insertSql);
$stmtCheck = $pdo->prepare($checkSql);

$defaults = [ 'id_categoria'=>1, 'id_marca'=>1, 'id_modelo'=>1, 'id_estatus'=>1, 'id_vendedor'=>1 ];

$added = 0;
foreach ($vehicles as $v) {
    $stmtCheck->execute([':vin'=>$v['vin']]);
    $row = $stmtCheck->fetch();
    if ($row && $row['cnt'] > 0) {
        echo "Ignorado VIN existente: {$v['vin']}\n";
        continue;
    }

    $params = [
        ':id_categoria' => $defaults['id_categoria'],
        ':id_marca' => $defaults['id_marca'],
        ':id_modelo' => $defaults['id_modelo'],
        ':id_estatus' => $defaults['id_estatus'],
        ':id_vendedor' => $defaults['id_vendedor'],
        ':anio' => $v['año'],
        ':precio' => $v['precio'],
        ':color' => $v['color'],
        ':vin' => $v['vin'],
        ':descripcion' => $v['descripcion'],
        ':foto' => $v['foto'],
    ];

    try {
        $stmtInsert->execute($params);
        $lastId = $pdo->lastInsertId();
        echo "Insertado vehiculo id={$lastId}, vin={$v['vin']}\n";
        $added++;
    } catch (Exception $e) {
        echo "Error insertando VIN {$v['vin']}: " . $e->getMessage() . "\n";
    }
}

echo "\nTotal agregados: {$added}\n";

?>
