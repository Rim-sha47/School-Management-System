<?php
/**
 * admin/includes/header.php
 * Reusable <head> block for all admin pages.
 * Usage: include at top of each admin page after defining $pageTitle.
 * Example: $pageTitle = "Manage Students";
 */
$pageTitle = $pageTitle ?? 'Admin Panel';
$schoolName = 'The Providence School';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?> | <?php echo $schoolName; ?></title>
<meta name="description" content="<?php echo $schoolName; ?> Admin Panel - <?php echo htmlspecialchars($pageTitle); ?>">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Global Styles -->
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
