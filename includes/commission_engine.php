<?php
// includes/commission_engine.php

/**
 * Distribute commissions and update network stats when a card is activated.
 *
 * @param PDO $pdo
 * @param int $user_id The ID of the user who purchased the card.
 * @param int $booking_id The ID of the booking.
 */
function process_card_activation($pdo, $user_id, $booking_id) {
    $is_manual_transaction = !$pdo->inTransaction();
    try {
        if ($is_manual_transaction) $pdo->beginTransaction();

        // 1. Get user and sponsor info
        $stmt = $pdo->prepare("SELECT sponsor_id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        $sponsor_id = ($user && isset($user['sponsor_id'])) ? $user['sponsor_id'] : null;

        if ($sponsor_id) {
            // 2. Direct Referral Incentive (Rs. 2,000)
        $direct_incentive = 2000;
        add_income($pdo, $sponsor_id, $direct_incentive, 'referral_incentive', "Direct referral incentive for user ID: $user_id");

        // Update direct sales count
        $pdo->prepare("INSERT INTO sales_stats (user_id, direct_sales, team_sales) VALUES (?, 1, 0)
                       ON DUPLICATE KEY UPDATE direct_sales = direct_sales + 1")->execute([$sponsor_id]);

        // 3. 8-Level Unilevel Commission & Team Sales Update
        $current_sponsor = $sponsor_id;
        for ($level = 1; $level <= 8; $level++) {
            if (!$current_sponsor) break;

            // Update team sales for every ancestor
            $pdo->prepare("INSERT INTO sales_stats (user_id, direct_sales, team_sales) VALUES (?, 0, 1)
                           ON DUPLICATE KEY UPDATE team_sales = team_sales + 1")->execute([$current_sponsor]);

            // Get level incentive amount
            $stmt = $pdo->prepare("SELECT card_sale_incentive FROM level_configs WHERE `level` = ?");
            $stmt->execute([$level]);
            $level_config = $stmt->fetch();

            if ($level_config) {
                $amount = $level_config['card_sale_incentive'];
                add_income($pdo, $current_sponsor, $amount, 'level_incentive', "Level $level incentive from user ID: $user_id");
            }

            // Check for Rank Upgrade
            check_and_upgrade_rank($pdo, $current_sponsor);

                // Move up to the next sponsor
                $stmt = $pdo->prepare("SELECT sponsor_id FROM users WHERE id = ?");
                $stmt->execute([$current_sponsor]);
                $result = $stmt->fetch();
                $current_sponsor = $result ? $result['sponsor_id'] : null;
            }
        }

        // 4. Update Booking Status
        $pdo->prepare("UPDATE bookings SET status = 'active', activation_date = NOW() WHERE id = ?")->execute([$booking_id]);

        if ($is_manual_transaction) $pdo->commit();
    } catch (Exception $e) {
        if ($is_manual_transaction && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

/**
 * Add income to user's wallet and log transaction
 */
function add_income($pdo, $user_id, $amount, $type, $description) {
    // Ensure wallet exists
    $pdo->prepare("INSERT IGNORE INTO wallets (user_id) VALUES (?)")->execute([$user_id]);

    // Update wallet
    $column = ($type == 'referral_incentive') ? 'referral_income' : (($type == 'level_incentive') ? 'level_income' : 'rank_income');
    $sql = "UPDATE wallets SET balance = balance + ?, $column = $column + ?, total_earned = total_earned + ? WHERE user_id = ?";
    $pdo->prepare($sql)->execute([$amount, $amount, $amount, $user_id]);

    // Log transaction
    $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, ?, ?)")
        ->execute([$user_id, $amount, $type, $description]);
}

/**
 * Check and upgrade user rank based on team sales
 */
function check_and_upgrade_rank($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT team_sales FROM sales_stats WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    if (!$stats) return;

    $team_sales = $stats['team_sales'];

    // Get the highest rank the user qualifies for
    $stmt = $pdo->prepare("SELECT `rank` FROM rank_configs WHERE team_sales_required <= ? ORDER BY team_sales_required DESC LIMIT 1");
    $stmt->execute([$team_sales]);
    $new_rank = $stmt->fetchColumn();

    if ($new_rank) {
        $stmt = $pdo->prepare("SELECT `rank` FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_rank = $stmt->fetchColumn();

        if ($new_rank != $current_rank) {
            $pdo->prepare("UPDATE users SET `rank` = ? WHERE id = ?")->execute([$new_rank, $user_id]);
        }
    }
}
?>
