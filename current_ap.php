<?php
$user_id = $_SESSION['user_id'];

// Fetch the current year
$current_year = date('Y');

// Query to get the goals for the current year
$goal_query = $conn->prepare("SELECT id FROM goal WHERE user_id = ? AND year = ?");
$goal_query->bind_param("ii", $user_id, $current_year);
$goal_query->execute();
$goal_result = $goal_query->get_result();

$goal_ids = [];
while ($row = $goal_result->fetch_assoc()) {
    $goal_ids[] = $row['id'];
}

if (empty($goal_ids)) {
    echo "<tr><td colspan='5'>No action plans available for the current year.</td></tr>";
} else {
    // Prepare placeholders for goal IDs
    $goal_ids_placeholders = implode(',', array_fill(0, count($goal_ids), '?'));

    // Search term (if provided)
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';

    // SQL query to retrieve action plans based on goals and optional search term
    $sql = "
        SELECT 
            action_plan.title, 
            action_plan.ap_description, 
            action_plan.budget, 
            goal.title AS goal_title 
        FROM action_plan
        JOIN goal ON action_plan.goal = goal.id
        WHERE goal.id IN ($goal_ids_placeholders)
    ";

    // If there's a search query, add conditions to SQL query
    if (!empty($search_query)) {
        $search_term = "%$search_query%";
        $sql .= "
            AND (
                action_plan.title LIKE ? 
                OR action_plan.ap_description LIKE ? 
                OR goal.title LIKE ?
            )
        ";
    }

    // Prepare the SQL query
    $ap_query = $conn->prepare($sql);

    // Bind parameters for goal IDs
    $types = str_repeat('i', count($goal_ids)); // 'ii...' for goal IDs
    $params = $goal_ids; // Array of goal IDs
    if (!empty($search_query)) {
        // Append search terms to types and params
        $types .= 'sss'; // 'ss...' for search terms
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    // Bind parameters dynamically
    $ap_query->bind_param($types, ...$params);

    // Execute the query
    $ap_query->execute();
    $ap_result = $ap_query->get_result();

    // Output the action plans in table rows
    $row_class = 'row-1';
    while ($row = $ap_result->fetch_assoc()) {
        echo "<tr class='$row_class'>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['ap_description']}</td>";
        echo "<td>{$row['budget']}</td>";
        echo "<td>{$row['goal_title']}</td>"; // Display the goal title
        echo "<td>
                <button class='edit-button'>Edit</button>
                <button class='archive-button'>Archive</button>
              </td>";
        echo "</tr>";

        // Toggle row class for alternating colors
        $row_class = ($row_class === 'row-1') ? 'row-2' : 'row-1';
    }
}

$goal_query->close();
$ap_query->close();
?>
