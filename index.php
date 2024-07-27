<?php
// Define the file paths
$reportFile = 'incidents/reports.json';
$arrestFile = 'incidents/arrests.json';

// Function to fetch all reports
function fetchReports() {
    global $reportFile;
    return file_exists($reportFile) ? json_decode(file_get_contents($reportFile), true) : [];
}

// Function to fetch all arrest reports
function fetchArrests() {
    global $arrestFile;
    return file_exists($arrestFile) ? json_decode(file_get_contents($arrestFile), true) : [];
}

// Function to add a new report
function addReport($officerName, $badgeNumber, $incidentType, $location, $description, $date) {
    global $reportFile;

    $reports = fetchReports();
    $newReport = [
        'id' => uniqid(),
        'officerName' => $officerName,
        'badgeNumber' => $badgeNumber,
        'incidentType' => $incidentType,
        'location' => $location,
        'description' => $description,
        'date' => $date,
        'comments' => []
    ];
    $reports[] = $newReport;
    file_put_contents($reportFile, json_encode($reports, JSON_PRETTY_PRINT));
}

// Function to add a new arrest report
function addArrest($officerName, $badgeNumber, $civilianName, $civilianID, $charges, $arrestDate, $location, $details) {
    global $arrestFile;

    $arrests = fetchArrests();
    $newArrest = [
        'id' => uniqid(),
        'officerName' => $officerName,
        'badgeNumber' => $badgeNumber,
        'civilianName' => $civilianName,
        'civilianID' => $civilianID,
        'charges' => $charges,
        'arrestDate' => $arrestDate,
        'location' => $location,
        'details' => $details
    ];
    $arrests[] = $newArrest;
    file_put_contents($arrestFile, json_encode($arrests, JSON_PRETTY_PRINT));
}

// Function to add a comment to a report
function addComment($reportId, $comment) {
    global $reportFile;
    $reports = fetchReports();
    foreach ($reports as &$report) {
        if ($report['id'] === $reportId) {
            $report['comments'][] = [
                'timestamp' => date('Y-m-d H:i:s'),
                'comment' => $comment
            ];
            break;
        }
    }
    file_put_contents($reportFile, json_encode($reports, JSON_PRETTY_PRINT));
}

// Function to search reports by title or incident type
function searchReports($searchTerm) {
    $reports = fetchReports();
    return array_filter($reports, function($report) use ($searchTerm) {
        return stripos($report['incidentType'], $searchTerm) !== false || stripos($report['description'], $searchTerm) !== false;
    });
}

// Function to search arrest reports by civilian name or charges
function searchArrests($searchTerm) {
    $arrests = fetchArrests();
    return array_filter($arrests, function($arrest) use ($searchTerm) {
        return stripos($arrest['civilianName'], $searchTerm) !== false || stripos($arrest['charges'], $searchTerm) !== false;
    });
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $officerName = $_POST['officerName'] ?? '';
            $badgeNumber = $_POST['badgeNumber'] ?? '';
            $incidentType = $_POST['incidentType'] ?? '';
            $location = $_POST['location'] ?? '';
            $description = $_POST['description'] ?? '';
            $date = $_POST['date'] ?? '';
            if ($officerName && $badgeNumber && $incidentType && $location && $description && $date) {
                addReport($officerName, $badgeNumber, $incidentType, $location, $description, $date);
                $message = 'Report added successfully!';
            } else {
                $message = 'Please fill in all fields.';
            }
        } elseif ($_POST['action'] === 'addArrest') {
            $officerName = $_POST['officerName'] ?? '';
            $badgeNumber = $_POST['badgeNumber'] ?? '';
            $civilianName = $_POST['civilianName'] ?? '';
            $civilianID = $_POST['civilianID'] ?? '';
            $charges = $_POST['charges'] ?? '';
            $arrestDate = $_POST['arrestDate'] ?? '';
            $location = $_POST['location'] ?? '';
            $details = $_POST['details'] ?? '';
            if ($officerName && $badgeNumber && $civilianName && $civilianID && $charges && $arrestDate && $location && $details) {
                addArrest($officerName, $badgeNumber, $civilianName, $civilianID, $charges, $arrestDate, $location, $details);
                $message = 'Arrest report added successfully!';
            } else {
                $message = 'Please fill in all fields.';
            }
        } elseif ($_POST['action'] === 'comment') {
            $reportId = $_POST['reportId'] ?? '';
            $comment = $_POST['comment'] ?? '';
            if ($reportId && $comment) {
                addComment($reportId, $comment);
                $message = 'Comment added successfully!';
            } else {
                $message = 'Please enter a comment.';
            }
        }
    }
}

// Handle search queries
$searchTerm = $_GET['search'] ?? '';
$searchResults = searchReports($searchTerm);
$searchArrestResults = searchArrests($searchTerm);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident and Arrest Report System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="text"], input[type="date"], textarea, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form label {
            display: flex;
            align-items: center;
        }
        .search-form i {
            margin-right: 10px;
            color: #007bff;
        }
        .comment-section {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .comment {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .comment form {
            display: flex;
            flex-direction: column;
        }
        .comment input[type="text"] {
            width: calc(100% - 20px);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fas fa-shield-alt"></i> Incident and Arrest Report System</h1>

    <!-- Add Report Form -->
    <h2><i class="fas fa-plus-circle"></i> Add New Incident Report</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <label>Officer Name: <input type="text" name="officerName" required></label>
        <label>Badge Number: <input type="text" name="badgeNumber" required></label>
        <label>Incident Type: 
            <select name="incidentType" required>
                <option value="">Select Type</option>
                <option value="Theft">Theft</option>
                <option value="Assault">Assault</option>
                <option value="Burglary">Burglary</option>
                <option value="Traffic Violation">Traffic Violation</option>
                <option value="Vandalism">Vandalism</option>
                <option value="Other">Other</option>
            </select>
        </label>
        <label>Location: <input type="text" name="location" required></label>
        <label>Description:<br><textarea name="description" rows="5" required></textarea></label>
        <label>Date: <input type="date" name="date" required></label>
        <input type="submit" value="Add Report">
    </form>

    <!-- Add Arrest Report Form -->
    <h2><i class="fas fa-user-arrest"></i> Add New Arrest Report</h2>
    <form method="POST">
        <input type="hidden" name="action" value="addArrest">
        <label>Officer Name: <input type="text" name="officerName" required></label>
        <label>Badge Number: <input type="text" name="badgeNumber" required></label>
        <label>Civilian Name: <input type="text" name="civilianName" required></label>
        <label>Civilian ID: <input type="text" name="civilianID" required></label>
        <label>Charges: <input type="text" name="charges" required></label>
        <label>Arrest Date: <input type="date" name="arrestDate" required></label>
        <label>Location: <input type="text" name="location" required></label>
        <label>Details:<br><textarea name="details" rows="5" required></textarea></label>
        <input type="submit" value="Add Arrest Report">
    </form>

    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <!-- Search Form -->
    <h2><i class="fas fa-search"></i> Search Incident and Arrest Reports</h2>
    <form method="GET" class="search-form">
        <label><i class="fas fa-search"></i> Search:
            <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
        </label>
        <input type="submit" value="Search">
    </form>

    <!-- Report Table -->
    <?php if (!empty($searchResults)): ?>
    <h2><i class="fas fa-list-ul"></i> Incident Reports</h2>
    <table>
        <thead>
            <tr>
                <th>Officer Name</th>
                <th>Badge Number</th>
                <th>Incident Type</th>
                <th>Location</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($searchResults as $report): ?>
            <tr>
                <td><?php echo htmlspecialchars($report['officerName']); ?></td>
                <td><?php echo htmlspecialchars($report['badgeNumber']); ?></td>
                <td><?php echo htmlspecialchars($report['incidentType']); ?></td>
                <td><?php echo htmlspecialchars($report['location']); ?></td>
                <td><?php echo htmlspecialchars($report['description']); ?></td>
                <td><?php echo htmlspecialchars($report['date']); ?></td>
            </tr>
            <tr>
                <td colspan="6">
                    <div class="comment-section">
                        <h3>Comments</h3>
                        <?php if (!empty($report['comments'])): ?>
                            <?php foreach ($report['comments'] as $comment): ?>
                                <div class="comment">
                                    <strong><?php echo htmlspecialchars($comment['timestamp']); ?>:</strong>
                                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No comments yet.</p>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="comment">
                            <input type="hidden" name="reportId" value="<?php echo htmlspecialchars($report['id']); ?>">
                            <label>Add Comment:</label>
                            <input type="text" name="comment" required>
                            <input type="submit" value="Add Comment">
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No reports found.</p>
    <?php endif; ?>

    <!-- Arrest Report Table -->
    <?php if (!empty($searchArrestResults)): ?>
    <h2><i class="fas fa-user-arrest"></i> Arrest Reports</h2>
    <table>
        <thead>
            <tr>
                <th>Officer Name</th>
                <th>Badge Number</th>
                <th>Civilian Name</th>
                <th>Civilian ID</th>
                <th>Charges</th>
                <th>Arrest Date</th>
                <th>Location</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($searchArrestResults as $arrest): ?>
            <tr>
                <td><?php echo htmlspecialchars($arrest['officerName']); ?></td>
                <td><?php echo htmlspecialchars($arrest['badgeNumber']); ?></td>
                <td><?php echo htmlspecialchars($arrest['civilianName']); ?></td>
                <td><?php echo htmlspecialchars($arrest['civilianID']); ?></td>
                <td><?php echo htmlspecialchars($arrest['charges']); ?></td>
                <td><?php echo htmlspecialchars($arrest['arrestDate']); ?></td>
                <td><?php echo htmlspecialchars($arrest['location']); ?></td>
                <td><?php echo htmlspecialchars($arrest['details']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No arrest reports found.</p>
    <?php endif; ?>

</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
