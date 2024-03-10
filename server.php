<?php
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$db1 = new SQLite3('all.db');

$courseInfoXML = <<<XML
<?xml version='1.0' standalone='yes'?>
<courses>
    <course>
        <title>CST8268 Web Programming Languages II</title>
        <facilitator>Oliver Gagnon</facilitator>
        <semester>4</semester>
    </course>
    <course>
        <title>CST8268 Project</title>
        <facilitator>Geeta Bhambhani</facilitator>
        <semester>4</semester>
    </course>
    <course>
        <title>CST8268 Current Trends in Web App. Dev.</title>
        <facilitator>Astha Tiwari</facilitator>
        <semester>4</semester>
    </course>
    <course>
        <title>CST8268 Web Security Basics</title>
        <facilitator>Steve Vaillancourt</facilitator>
        <semester>4</semester>
    </course>
</courses>
XML;

//create tables if they don't exist
$db1->exec('CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY, 
    subject TEXT)');
$db1->exec('CREATE TABLE IF NOT EXISTS assignments (
    id INTEGER PRIMARY KEY,
    subjectTitle TEXT,
    assignmentName TEXT,
    tasks TEXT,
    dueDate TEXT,
    completed BOOLEAN
    );');

$fetchAllFromSubjs = $db1->query('SELECT COUNT(*) FROM subjects');
$subjCount = $fetchAllFromSubjs->fetchArray(SQLITE3_ASSOC)['COUNT(*)'];

// If subjects table is empty, insert my subjects
if ($subjCount == 0) {
    $db1->exec("INSERT INTO subjects (subject) VALUES ('Project')");
    $db1->exec("INSERT INTO subjects (subject) VALUES ('Web Programming Languages II')");
    $db1->exec("INSERT INTO subjects (subject) VALUES ('Web Security Basics')");
    $db1->exec("INSERT INTO subjects (subject) VALUES ('Current Trends in Web App. Dev.')");
}
$resource = isset($_GET['resource']) ? $_GET['resource'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($resource === 'subjects') {
        $fetchAllFromSubjs = $db1->query('SELECT * FROM subjects');
        $data = [];
        while ($row = $fetchAllFromSubjs->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }
        echo json_encode($data);
    } 
    elseif ($resource === 'assignments') {
        $fetchAllFromSubjs = $db1->query('SELECT * FROM assignments');
        $assignments = [];
        while ($row = $fetchAllFromSubjs->fetchArray(SQLITE3_ASSOC)) {
            $assignments[] = $row;
        }
        echo json_encode($assignments);
    }     
    elseif ($resource === 'courseInfoXML') {
        header('application/xml');
        echo($courseInfoXML);
    }

 } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectTitle = $_POST['subjectTitle'] ?? '';
    $assignmentName = $_POST['assignmentName'] ?? '';
    $tasks = $_POST['tasks'] ?? '';
    $dueDate = $_POST['dueDate'] ?? '';
    
    $stmt = $db1->prepare(
        "INSERT INTO assignments (subjectTitle, assignmentName, tasks, dueDate) 
        VALUES (:subjectTitle, :assignmentName, :tasks, :dueDate)"
        );
    $stmt->bindValue(':subjectTitle', $subjectTitle, SQLITE3_TEXT);
    $stmt->bindValue(':assignmentName', $assignmentName, SQLITE3_TEXT);
    $stmt->bindValue(':tasks', $tasks, SQLITE3_TEXT);
    $stmt->bindValue(':dueDate', $dueDate, SQLITE3_TEXT);
    
    $fetchAllFromSubjs = $stmt->execute();
}



$db1->close();

