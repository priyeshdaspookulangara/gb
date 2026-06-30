<?php
// includes/point_engine.php

/**
 * Distribute commissions for a product sale based on points.
 *
 * @param PDO $pdo
 * @param int $user_id The promoter who made the sale.
 * @param int $points Number of points in the sale.
 */
function process_product_sale($pdo, $user_id, $points) {
    try {
        $pdo->beginTransaction();

        $current_sponsor = $user_id;
        for ($level = 1; $level <= 8; $level++) {
            if (!$current_sponsor) break;

            // Get product sale incentive per point for this level
            $stmt = $pdo->prepare("SELECT product_sale_per_point FROM level_configs WHERE `level` = ?");
            $stmt->execute([$level]);
            $per_point = $stmt->fetchColumn();

            if ($per_point) {
                $amount = $per_point * $points;
                add_income($pdo, $current_sponsor, $amount, 'level_incentive', "Product Sale Level $level Incentive ($points points)");
            }

            // Move up
            $stmt = $pdo->prepare("SELECT sponsor_id FROM users WHERE id = ?");
            $stmt->execute([$current_sponsor]);
            $current_sponsor = $stmt->fetchColumn();
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

/**
 * Update daily reward points for customers based on system-wide sales.
 * (Simulates the requirement: Daily point accumulation updates based on system-wide bullion sales metrics)
 */
function update_daily_customer_points($pdo) {
    $metric = (float)get_setting($pdo, 'bullion_sales_metric');
    $points_to_add = floor($metric / 1000); // 1 point per Rs. 1000 system sales

    if ($points_to_add > 0) {
        $pdo->query("UPDATE wallets w
                    JOIN users u ON w.user_id = u.id
                    SET w.reward_points = w.reward_points + $points_to_add
                    WHERE u.role = 'customer'");
    }
}
?>
