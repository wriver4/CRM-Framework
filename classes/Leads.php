<?php

class Leads {
    private $db;

    public function __construct() {
        $this->db = new Database(); // Assuming a Database class exists
    }

    public function create_lead($data) {
        // SQL to insert a new lead
        $sql = "INSERT INTO leads (family_name, phone, email, address, estimate_number, stage, proposal_sent_date, scheduled_date, structure_type, lead_source, lead_lost_notes, plans_submitted, structure_description, structure_other, site_visit_by, picture_submitted, referred_to, picture_upload_link, plans_upload_link, existing_client, get_updates, hear_about, hear_about_other, structure_additional, lead_notes, prospect_notes, lead_lost, site_visit_completed, closer, referred_services, assigned_to, referred, site_visit_date, date_qualified, contacted_date, referral_done, jd_referral_notes, closing_notes, prospect_lost, to_contracting, plans_and_pics) VALUES (:name, :phone, :email, :address, :estimate_number, :stage, :proposal_sent_date, :scheduled_date, :structure_type, :lead_source, :lead_lost_notes, :plans_submitted, :structure_description, :structure_other, :site_visit_by, :picture_submitted, :referred_to, :picture_upload_link, :plans_upload_link, :existing_client, :get_updates, :hear_about, :hear_about_other, :structure_additional, :lead_notes, :prospect_notes, :lead_lost, :site_visit_completed, :closer, :referred_services, :assigned_to, :referred, :site_visit_date, :date_qualified, :contacted_date, :referral_done, :jd_referral_notes, :closing_notes, :prospect_lost, :to_contracting, :plans_and_pics)";
        return $this->db->execute($sql, $data);
    }

    public function get_leads() {
        // SQL to fetch all leads
        $sql = "SELECT * FROM leads";
        return $this->db->query($sql);
    }

    public function get_lead_by_id($id) {
        // SQL to fetch a lead by ID
        $sql = "SELECT * FROM leads WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    public function update_lead($id, $data) {
        // SQL to update a lead
        $sql = "UPDATE leads SET name = :name, phone = :phone, email = :email, address = :address, estimate_number = :estimate_number, stage = :stage, proposal_sent_date = :proposal_sent_date, scheduled_date = :scheduled_date, structure_type = :structure_type, lead_source = :lead_source, lead_lost_notes = :lead_lost_notes, plans_submitted = :plans_submitted, structure_description = :structure_description, structure_other = :structure_other, site_visit_by = :site_visit_by, picture_submitted = :picture_submitted, referred_to = :referred_to, picture_upload_link = :picture_upload_link, plans_upload_link = :plans_upload_link, existing_client = :existing_client, get_updates = :get_updates, hear_about = :hear_about, hear_about_other = :hear_about_other, structure_additional = :structure_additional, lead_notes = :lead_notes, prospect_notes = :prospect_notes, lead_lost = :lead_lost, site_visit_completed = :site_visit_completed, closer = :closer, referred_services = :referred_services, assigned_to = :assigned_to, referred = :referred, site_visit_date = :site_visit_date, date_qualified = :date_qualified, contacted_date = :contacted_date, referral_done = :referral_done, jd_referral_notes = :jd_referral_notes, closing_notes = :closing_notes, prospect_lost = :prospect_lost, to_contracting = :to_contracting, plans_and_pics = :plans_and_pics WHERE id = :id";
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function delete_lead($id) {
        // SQL to delete a lead
        $sql = "DELETE FROM leads WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
}