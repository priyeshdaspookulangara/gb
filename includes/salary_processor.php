<?php
// includes/salary_processor.php

/**
 * Process monthly rank maintenance incentives (salaries).
 * Should be run once a month.
 *
 * @param PDO $pdo
 */
function process_monthly_salaries($pdo) {
    try {
        $pdo->beginTransaction();

        // Get all users with a rank
        $stmt = $pdo->query("SELECT id, `rank` FROM users WHERE `rank` != 'NONE'");
        $ranked_users = $stmt->fetchAll();

        foreach ($ranked_users as $user) {
            $user_id = $user['id'];
            $rank = $user['rank'];

            // Get monthly incentive for this rank
            $stmt = $pdo->prepare("SELECT monthly_incentive FROM rank_configs WHERE `rank` = ?");
            $stmt->execute([$rank]);
            $amount = $stmt->fetchColumn();

            if ($amount > 0) {
                // Check how many times this has been paid
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM monthly_incentives_log WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $paid_count = $stmt->fetchColumn();

                if ($paid_count < 10) {
                    $month_number = $paid_count + 1;

                    // Add income
                    add_income($pdo, $user_id, $amount, 'monthly_incentive', "Monthly Rank Incentive for $rank (Month $month_number)");

                    // Log the payment
                    $stmt = $pdo->prepare("INSERT INTO monthly_incentives_log (user_id, `rank`, amount, month_number) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $rank, $amount, $month_number]);
                }
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Salary Processing Error: " . $e->getMessage());
        return false;
    }
}
?>
