<?php

namespace Altum\Models;

defined('ALTUMCODE') || die();

class SetupProject extends Model {

    public function ensure_table() {
        database()->query("
            CREATE TABLE IF NOT EXISTS `setup_projects` (
                `setup_project_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `industry` varchar(64) NOT NULL,
                `business_name` varchar(128) DEFAULT NULL,
                `status` varchar(32) NOT NULL DEFAULT 'new',
                `recommended_plan_id` varchar(32) DEFAULT NULL,
                `recommended_plan_name` varchar(128) DEFAULT NULL,
                `recommendation` text DEFAULT NULL,
                `details` text DEFAULT NULL,
                `assets` text DEFAULT NULL,
                `admin_notes` text DEFAULT NULL,
                `datetime` datetime NOT NULL,
                `last_datetime` datetime NOT NULL,
                PRIMARY KEY (`setup_project_id`),
                KEY `user_id` (`user_id`),
                KEY `status` (`status`),
                KEY `datetime` (`datetime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function get_latest_by_user_id($user_id) {
        $this->ensure_table();

        $project = db()->where('user_id', $user_id)->orderBy('setup_project_id', 'DESC')->getOne('setup_projects');

        if($project) {
            $project->details = json_decode($project->details ?? '');
            $project->assets = json_decode($project->assets ?? '');
            $project->recommendation = json_decode($project->recommendation ?? '');
        }

        return $project;
    }

    public function get_by_user_id_and_project_id($user_id, $setup_project_id) {
        $this->ensure_table();

        $project = db()
            ->where('user_id', $user_id)
            ->where('setup_project_id', $setup_project_id)
            ->getOne('setup_projects');

        if($project) {
            $project->details = json_decode($project->details ?? '');
            $project->assets = json_decode($project->assets ?? '');
            $project->recommendation = json_decode($project->recommendation ?? '');
        }

        return $project;
    }

    public function recommend_plan($details, $pricing_context = 'recurring') {
        $industry = $details['industry'] ?? 'other';
        $is_hospitality = in_array($industry, ['restaurant', 'hotel', 'lounge', 'catering']);

        $effective_locations = 1;
        $flipbooks_needed = 0;
        $biolinks_needed = 0;
        $qr_codes_needed = 0;
        $vcards_needed = 0;

        if($is_hospitality) {
            $branches_count = max(1, (int) ($details['branches_count'] ?? 1));
            $hotel_outlets_count = max(1, (int) ($details['hotel_outlets_count'] ?? 1));
            $menu_categories_count = max(1, (int) ($details['menu_categories_count'] ?? 1));
            $effective_locations = $industry == 'hotel' ? $hotel_outlets_count : $branches_count;

            $flipbooks_needed = $menu_categories_count * $effective_locations;
            $biolinks_needed = $effective_locations * 2;
            $qr_codes_needed = $effective_locations;
        } elseif($industry == 'corporate') {
            $staff_cards_count = max(1, (int) ($details['staff_cards_count'] ?? 1));

            $vcards_needed = $staff_cards_count;
            $qr_codes_needed = $staff_cards_count;
        } else {
            $biolinks_needed = 1;
            $qr_codes_needed = 1;
        }

        $requirements = [
            'effective_locations' => $effective_locations,
            'flipbooks_needed' => $flipbooks_needed,
            'biolinks_needed' => $biolinks_needed,
            'qr_codes_needed' => $qr_codes_needed,
            'vcards_needed' => $vcards_needed,
        ];

        $recommended_plan = null;
        $plans = $this->get_public_pricing_plans($pricing_context);
        $public_plan_limits = $this->get_public_plan_limits();

        foreach(array_values($plans) as $index => $plan) {
            $limits = $public_plan_limits[$index] ?? end($public_plan_limits);
            $covers = true;
            $covers = $covers && $this->limit_covers($limits['flipbooks'], $flipbooks_needed);
            $covers = $covers && $this->limit_covers($limits['biolinks'], $biolinks_needed);
            $covers = $covers && $this->limit_covers($limits['qr_codes'], $qr_codes_needed);

            if($covers) {
                $recommended_plan = $plan;
                $recommended_limits = $limits;
                break;
            }
        }

        if(!$recommended_plan) {
            $recommended_plan = end($plans) ?: settings()->plan_custom;
            $recommended_limits = end($public_plan_limits);
        }

        return [
            'requirements' => $requirements,
            'plan_id' => $recommended_plan->plan_id ?? 'custom',
            'plan_name' => $recommended_plan->name ?? 'Custom',
            'plan_limits' => $recommended_limits ?? null,
            'pricing_context' => $pricing_context,
        ];
    }

    private function get_public_plan_limits() {
        return [
            [
                'qr_codes' => 8,
                'biolinks' => 8,
                'flipbooks' => 2,
            ],
            [
                'qr_codes' => 200,
                'biolinks' => 20,
                'flipbooks' => 6,
            ],
            [
                'qr_codes' => -1,
                'biolinks' => 50,
                'flipbooks' => 15,
            ],
        ];
    }

    private function get_public_pricing_plans($pricing_context) {
        $plans = [];

        foreach((new Plan())->get_plans() as $plan) {
            if($plan->status != 1) continue;

            if($pricing_context == 'lifetime') {
                if(empty($plan->prices->lifetime->{currency()})) continue;
            } else {
                $has_recurring_price =
                    !empty($plan->prices->monthly->{currency()}) ||
                    !empty($plan->prices->quarterly->{currency()}) ||
                    !empty($plan->prices->biannual->{currency()}) ||
                    !empty($plan->prices->annual->{currency()});

                if(!$has_recurring_price) continue;
                if(!empty($plan->prices->lifetime->{currency()})) continue;
            }

            $plans[$plan->plan_id] = $plan;

            if(count($plans) >= 3) {
                break;
            }
        }

        return $plans;
    }

    private function limit_covers($limit, $needed) {
        $limit = (int) $limit;
        $needed = (int) $needed;

        if($needed <= 0) return true;
        if($limit == -1) return true;

        return $limit >= $needed;
    }
}
