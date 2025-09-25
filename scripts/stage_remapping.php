<?php
/**
 * Stage Remapping Configuration Script
 * 
 * This script defines the new stage numbering system with numeric spaces
 * and allows developers to easily modify stage mappings.
 * 
 * IMPORTANT: After modifying this file, run the migration script to update the database.
 */

class StageRemapping {
    
    /**
     * New stage mapping with 10-unit increments for future expansion
     * Format: new_stage_number => ['name' => 'Stage Name', 'old_stage' => old_number]
     */
    public static function getNewStageMapping() {
        return [
            10 => ['name' => 'Lead', 'old_stage' => 1],
            20 => ['name' => 'Pre-Qualification', 'old_stage' => 2],
            30 => ['name' => 'Qualified', 'old_stage' => 3],
            40 => ['name' => 'Referral', 'old_stage' => 4],
            50 => ['name' => 'Prospect', 'old_stage' => 5],
            60 => ['name' => 'Prelim Design', 'old_stage' => 6],
            70 => ['name' => 'Manufacturing Estimate', 'old_stage' => 7],
            80 => ['name' => 'Contractor Estimate', 'old_stage' => 8],
            90 => ['name' => 'Completed Estimate', 'old_stage' => 9],
            100 => ['name' => 'Prospect Response', 'old_stage' => 10],
            110 => ['name' => 'Closing Conference', 'old_stage' => 11],
            120 => ['name' => 'Potential Client Response', 'old_stage' => 12],
            // MOVED: Closed Won and Closed Lost come before Contracting
            130 => ['name' => 'Closed Won', 'old_stage' => 14],
            140 => ['name' => 'Closed Lost', 'old_stage' => 15],
            150 => ['name' => 'Contracting', 'old_stage' => 13],
        ];
    }
    
    /**
     * Reverse mapping: old stage number => new stage number
     */
    public static function getOldToNewMapping() {
        $mapping = [];
        foreach (self::getNewStageMapping() as $newStage => $data) {
            $mapping[$data['old_stage']] = $newStage;
        }
        return $mapping;
    }
    
    /**
     * Get stage progressions with new numbering
     */
    public static function getNewStageProgressions() {
        return [
            10 => [20, 30, 40, 50, 140], // Lead -> Pre-Qualification, Qualified, Referral, Prospect, Closed Lost
            20 => [30, 40, 50, 140],     // Pre-Qualification -> Qualified, Referral, Prospect, Closed Lost
            30 => [40, 50, 140],         // Qualified -> Referral, Prospect, Closed Lost
            40 => [50, 140],             // Referral -> Prospect, Closed Lost (trigger stage)
            50 => [60, 140],             // Prospect -> Prelim Design, Closed Lost (trigger stage)
            60 => [70, 140],             // Prelim Design -> Manufacturing Estimate, Closed Lost
            70 => [80, 140],             // Manufacturing Estimate -> Contractor Estimate, Closed Lost
            80 => [90, 140],             // Contractor Estimate -> Completed Estimate, Closed Lost
            90 => [100, 140],            // Completed Estimate -> Prospect Response, Closed Lost
            100 => [110, 140],           // Prospect Response -> Closing Conference, Closed Lost
            110 => [120, 140],           // Closing Conference -> Potential Client Response, Closed Lost
            120 => [130, 140, 150],      // Potential Client Response -> Closed Won, Closed Lost, Contracting
            130 => [],                   // Closed Won (final stage)
            140 => [],                   // Closed Lost (final stage - trigger stage)
            150 => [130, 140],           // Contracting -> Closed Won, Closed Lost
        ];
    }
    
    /**
     * Get trigger stages with new numbering
     */
    public static function getTriggerStages() {
        return [40, 50, 140]; // Referral, Prospect, Closed Lost
    }
    
    /**
     * Get stage categories with new numbering
     */
    public static function getStageCategories() {
        return [
            'qualification' => [10, 20, 30],           // Lead, Pre-Qualification, Qualified
            'referral' => [40],                        // Referral
            'prospect_development' => [50, 60, 70, 80, 90], // Prospect through Completed Estimate
            'closing' => [100, 110, 120],              // Prospect Response through Potential Client Response
            'won' => [130],                            // Closed Won
            'lost' => [140],                           // Closed Lost
            'contracting' => [150],                    // Contracting
        ];
    }
    
    /**
     * Get stage badge classes with new numbering
     */
    public static function getStageBadgeClasses() {
        return [
            10 => 'badge bg-primary',      // Lead
            20 => 'badge bg-info',         // Pre-Qualification
            30 => 'badge bg-warning',      // Qualified
            40 => 'badge bg-info',         // Referral
            50 => 'badge bg-warning',      // Prospect
            60 => 'badge bg-warning',      // Prelim Design
            70 => 'badge bg-warning',      // Manufacturing Estimate
            80 => 'badge bg-warning',      // Contractor Estimate
            90 => 'badge bg-success',      // Completed Estimate
            100 => 'badge bg-success',     // Prospect Response
            110 => 'badge bg-success',     // Closing Conference
            120 => 'badge bg-success',     // Potential Client Response
            130 => 'badge bg-success',     // Closed Won
            140 => 'badge bg-danger',      // Closed Lost
            150 => 'badge bg-success',     // Contracting
        ];
    }
    
    /**
     * Get module stage filters with new numbering
     */
    public static function getModuleStageFilters() {
        return [
            'leads' => [10, 20, 30, 40, 50, 140],      // Lead through Prospect + Closed Lost
            'prospects' => [50, 60, 70, 80, 90, 100, 110, 120, 150], // Prospect through Contracting
            'referrals' => [40],                        // Referral only
            'contracting' => [150],                     // Contracting only
        ];
    }
}