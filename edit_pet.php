<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid pet specified.";
    header('Location: dashboard.php');
    exit();
}

$pet = null;
try {
    $stmt = $pdo->prepare("SELECT id, name, breed, age FROM pets WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $pet = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching pet for edit: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not load pet data.";
    header('Location: dashboard.php');
    exit();
}

if (!$pet) {
    $_SESSION['error_message'] = "Pet not found or you don't have permission to edit it.";
    header('Location: dashboard.php');
    exit();
}

$pageTitle = "Edit Pet";
include 'header.php';
?>

<main class="form-page">
    <div class="form-container" style="max-width: 500px;">
        <div class="form-box">
            <div class="form-header">
                <h2>Edit <?php echo htmlspecialchars($pet['name']); ?>'s Details</h2>
            </div>
            <form action="process_update_pet.php" method="POST">
                <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                <div class="form-group">
                    <label for="pet_name">Pet's Name</label>
                    <input type="text" id="pet_name" name="pet_name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="pet_breed">Breed</label>
                    <input type="text" id="pet_breed" name="pet_breed" value="<?php echo htmlspecialchars($pet['breed']); ?>">
                </div>
                <div class="form-group">
                    <label for="pet_age">Age</label>
                    <input type="number" id="pet_age" name="pet_age" value="<?php echo htmlspecialchars($pet['age']); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-full-green">Save Changes</button>
            </form>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
