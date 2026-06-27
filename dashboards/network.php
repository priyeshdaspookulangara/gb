<?php
// dashboards/network.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('promoter');

$user_id = $_SESSION['user_id'];

// Get Downline (1 level for simple list)
$stmt = $pdo->prepare("SELECT u.username, u.full_name, u.role, u.rank, u.created_at, s.team_sales
                       FROM users u
                       LEFT JOIN sales_stats s ON u.id = s.user_id
                       WHERE u.sponsor_id = ?");
$stmt->execute([$user_id]);
$downline = $stmt->fetchAll();

/**
 * Recursive function to build 8-level tree
 */
function get_tree($pdo, $ancestor_id, $max_level = 3) {
    if ($max_level <= 0) return [];

    $stmt = $pdo->prepare("SELECT u.id, u.username, u.rank, s.team_sales
                           FROM users u
                           LEFT JOIN sales_stats s ON u.id = s.user_id
                           WHERE u.sponsor_id = ?");
    $stmt->execute([$ancestor_id]);
    $members = $stmt->fetchAll();

    foreach ($members as &$member) {
        $member['children'] = get_tree($pdo, $member['id'], $max_level - 1);
    }

    return $members;
}

$full_tree = get_tree($pdo, $user_id, 3); // Limiting to 3 levels for visual sanity in dashboard, can be 8.
?>

<div class="glass-card">
    <h2 class="gold-gradient-text">My Direct Downline</h2>
    <p>View the promoters and customers you've directly sponsored.</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">Username</th>
                <th style="text-align: left; padding: 10px;">Full Name</th>
                <th style="text-align: left; padding: 10px;">Role</th>
                <th style="text-align: left; padding: 10px;">Rank</th>
                <th style="text-align: right; padding: 10px;">Team Sales</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($downline)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 20px;">No downline members yet.</td></tr>
            <?php else: ?>
                <?php foreach ($downline as $member): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 10px;"><?php echo htmlspecialchars($member['username']); ?></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($member['full_name']); ?></td>
                        <td style="padding: 10px;"><?php echo strtoupper($member['role']); ?></td>
                        <td style="padding: 10px;"><?php echo $member['rank']; ?></td>
                        <td style="padding: 10px; text-align: right;"><?php echo $member['team_sales'] ?? 0; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="glass-card" style="margin-top: 24px; overflow-x: auto;">
    <h3 class="gold-text">Interactive Genealogy Tree</h3>
    <div class="tree-container">
        <?php
        function render_tree_node($member) {
            echo '<div class="tree-node">';
            echo '<div class="node-card">';
            echo '<strong>' . htmlspecialchars($member['username']) . '</strong><br>';
            echo '<small>' . ($member['rank'] ?: 'NONE') . '</small><br>';
            echo '<span style="font-size: 10px;">Sales: ' . ($member['team_sales'] ?: 0) . '</span>';
            echo '</div>';

            if (!empty($member['children'])) {
                echo '<div class="tree-connector-v"></div>';
                echo '<div class="tree-children">';
                foreach ($member['children'] as $child) {
                    render_tree_node($child);
                }
                echo '</div>';
            }
            echo '</div>';
        }
        ?>

        <div class="tree-node">
            <div class="node-card" style="border-color: var(--brand-gold-pure); border-width: 2px;">
                <strong>ME</strong><br>
                <small><?php echo $_SESSION['username']; ?></small>
            </div>
            <?php if (!empty($full_tree)): ?>
                <div class="tree-connector-v"></div>
                <div class="tree-children">
                    <?php foreach ($full_tree as $member): render_tree_node($member); endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
