<?php
function getDB() {
    $db = new SQLite3('pcpartsniper.db');
    $db->busyTimeout(5000);
    return $db;
}

function query($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    
    if ($params) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    
    $result = $stmt->execute();
    return $result;
}

function fetchAll($sql, $params = []) {
    $result = query($sql, $params);
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function fetchOne($sql, $params = []) {
    $result = query($sql, $params);
    return $result->fetchArray(SQLITE3_ASSOC);
}

function execute($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    
    if ($params) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    
    return $stmt->execute();
}
?>
