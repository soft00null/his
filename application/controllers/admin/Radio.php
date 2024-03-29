<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Radio extends Admin_Controller {

    function __construct() {
        parent::__construct();
        $this->config->load("payroll");
        $this->search_type = $this->config->item('search_type');
        $this->load->model("report_model");
    }

    public function unauthorized() {
        $data = array();
        $this->load->view('layout/header', $data);
        $this->load->view('unauthorized', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add() {
        if (!$this->rbac->hasPrivilege('radiology test', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('test_name', $this->lang->line('test') . " " . $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('short_name', $this->lang->line('short') . " " . $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('test_type', $this->lang->line('test') . " " . $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('radiology_category_id', $this->lang->line('category') . " " . $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('charge_category_id', $this->lang->line('charge') . " " . $this->lang->line('category'), 'required');
        $this->form_validation->set_rules('code', $this->lang->line('code'), 'required');
        $this->form_validation->set_rules('standard_charge', $this->lang->line('standard') . " " . $this->lang->line('charge'), 'required');
        if ($this->form_validation->run() == FALSE) {
            $msg = array(
                'test_name' => form_error('test_name'),
                'short_name' => form_error('short_name'),
                'test_type' => form_error('test_type'),
                'radiology_category_id' => form_error('radiology_category_id'),
                'charge_category_id' => form_error('charge_category_id'),
                'code' => form_error('code'),
                'standard_charge' => form_error('standard_charge')
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $radiology = array(
                'test_name' => $this->input->post('test_name'),
                'short_name' => $this->input->post('short_name'),
                'test_type' => $this->input->post('test_type'),
                'radiology_category_id' => $this->input->post('radiology_category_id'),
                'sub_category' => $this->input->post('sub_category'),
                'report_days' => $this->input->post('report_days'),
                'charge_id' => $this->input->post('code')
            );
            $this->radio_model->add($radiology);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function search() {
        if (!$this->rbac->hasPrivilege('radiology test', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'radiology');
        $categoryName = $this->lab_model->getlabName();
        $data["categoryName"] = $categoryName;
        $data['charge_category'] = $this->radio_model->getChargeCategory();
        $doctors = $this->staff_model->getStaffbyrole(3);
        $data["doctors"] = $doctors;
        $data['resultlist'] = $this->radio_model->searchFullText();
        $result = $this->radio_model->getRadiology();
        $data['result'] = $result;
        $this->load->view('layout/header');
        $this->load->view('admin/radio/search.php', $data);
        $this->load->view('layout/footer');
    }

    public function getDetails() {
        if (!$this->rbac->hasPrivilege('radiology test', 'can_view')) {
            access_denied();
        }
        $id = $this->input->post("radiology_id");
        $result = $this->radio_model->getDetails($id);
        echo json_encode($result);
    }

    public function update() {
        if (!$this->rbac->hasPrivilege('radiology test', 'can_edit')) {
            access_denied();
        }
        $this->form_validation->set_rules('test_name', $this->lang->line('test') . " " . $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('short_name', $this->lang->line('short') . " " . $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('test_type', $this->lang->line('test') . " " . $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('radiology_category_id', $this->lang->line('category') . " " . $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('charge_category_id', $this->lang->line('charge') . " " . $this->lang->line('category'), 'required');
        if ($this->form_validation->run() == FALSE) {
            $msg = array(
                'test_name' => form_error('test_name'),
                'short_name' => form_error('short_name'),
                'test_type' => form_error('test_type'),
                'radiology_category_id' => form_error('radiology_category_id'),
                'charge_category_id' => form_error('charge_category_id')
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $id = $this->input->post('id');
            $charge_category_id = $this->input->post('charge_category_id');
            $radiology = array(
                'id' => $id,
                'test_name' => $this->input->post('test_name'),
                'short_name' => $this->input->post('short_name'),
                'test_type' => $this->input->post('test_type'),
                'radiology_category_id' => $this->input->post('radiology_category_id'),
                'sub_category' => $this->input->post('sub_category'),
                'report_days' => $this->input->post('report_days'),
                'charge_id' => $charge_category_id
            );
            $this->radio_model->update($radiology);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function delete($id) {
        if (!$this->rbac->hasPrivilege('radiology test', 'can_delete')) {
            access_denied();
        }
        if (!empty($id)) {
            $this->radio_model->delete($id);
            $array = array('status' => 'success', 'error' => '', 'message' => 'Record Deleted Successfully.');
        } else {
            $array = array('status' => 'fail', 'error' => '', 'message' => '');
        }
        echo json_encode($array);
    }

    public function getRadiology() {
        if (!$this->rbac->hasPrivilege('radiology test', 'can_view')) {
            access_denied();
        }
        $id = $this->input->post('radiology_id');
        $result = $this->radio_model->getRadiology($id);
        echo json_encode($result);
    }

    public function testReportBatch() {
        if (!$this->rbac->hasPrivilege('add_patient_test_reprt', 'can_add')) {
            access_denied();
        }
        if (!empty($_FILES['radiology_report']['name'])) {
            $config['upload_path'] = 'uploads/radiology_report/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['file_name'] = $_FILES['radiology_report']['name'];
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            if ($this->upload->do_upload('radiology_report')) {
                $uploadData = $this->upload->data();
                $picture = $uploadData['file_name'];
            } else {
                $picture = '';
            }
        } else {
            $picture = '';
        }
        $this->form_validation->set_rules('radiology_id', $this->lang->line('patient') . " " . $this->lang->line('id'), 'required');

        $this->form_validation->set_rules('patient_name', $this->lang->line('patient') . " " . $this->lang->line('name'), 'required');
        if ($this->form_validation->run() == FALSE) {
            $msg = array(
                'radiology_id' => form_error('radiology_id'),
                'patient_name' => form_error('patient_name'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $id = $this->input->post('radiology_id');
            $patient_id = $this->input->post('patient_id');
            $reporting_date = $this->input->post("reporting_date");

            $report_batch = array(
                'radiology_id' => $id,
                'patient_id' => $patient_id,
                'customer_type' => $this->input->post('customer_type'),
                'patient_name' => $this->input->post('patient_name'),
                'consultant_doctor' => $this->input->post('consultant_doctor'),
                'reporting_date' => date('Y-m-d', $this->customlib->datetostrtotime($reporting_date)),
                'description' => $this->input->post('description'),
                'radiology_report' => $picture,
                'apply_charge' => $this->input->post('apply_charge')
            );
            $this->radio_model->testReportBatch($report_batch);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function getTestReportBatch() {
        if (!$this->rbac->hasPrivilege('add_patient_test_reprt', 'can_view')) {
            access_denied();
        }
        $id = $this->input->post("radiology_id");
        $doctors = $this->staff_model->getStaffbyrole(3);
        $data["doctors"] = $doctors;
        $result = $this->radio_model->getTestReportBatch($id);
        $data["result"] = $result;
        $this->load->view('layout/header');
        $this->load->view('admin/radio/reportDetail', $data);
        $this->load->view('layout/footer');
    }

    public function getRadiologyReport() {
        if (!$this->rbac->hasPrivilege('add_patient_test_reprt', 'can_view')) {
            access_denied();
        }
        $id = $this->input->post('id');
        $result = $this->radio_model->getRadiologyReport($id);
        $result['reporting_date'] = date($this->customlib->getSchoolDateFormat(), strtotime($result['reporting_date']));

        echo json_encode($result);
    }

    public function updateTestReport() {
        if (!$this->rbac->hasPrivilege('add_patient_test_reprt', 'can_edit')) {
            access_denied();
        }
        if (!empty($_FILES['radiology_report']['name'])) {
            $config['upload_path'] = 'uploads/radiology_report/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['file_name'] = $_FILES['radiology_report']['name'];
            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            if ($this->upload->do_upload('radiology_report')) {
                $uploadData = $this->upload->data();
                $picture = $uploadData['file_name'];
            } else {
                $picture = '';
            }
        } else {
            $picture = '';
        }
        $this->form_validation->set_rules('id', 'Id', 'required');

        $this->form_validation->set_rules('patient_name', $this->lang->line('patient') . " " . $this->lang->line('name'), 'required');
        if ($this->form_validation->run() == FALSE) {
            $msg = array(
                'id' => form_error('id'),
                'patient_name' => form_error('patient_name'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $id = $this->input->post('id');
            $reporting_date = $this->input->post("reporting_date");

            $report_batch = array(
                'id' => $id,
                'customer_type' => $this->input->post('customer_type'),
                'consultant_doctor' => $this->input->post('consultant_doctor'),
                'reporting_date' => date('Y-m-d', $this->customlib->datetostrtotime($reporting_date)),
                'description' => $this->input->post('description'),
                'radiology_report' => $picture,
                'apply_charge' => $this->input->post('apply_charge'),
            );
            $this->radio_model->updateTestReport($report_batch);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function download($doc) {
        $this->load->helper('download');
        $filepath = "./uploads/radiology_report/" . $doc;
        $data = file_get_contents($filepath);
        force_download($doc, $data);
    }

    public function deleteTestReport($id) {
        if (!$this->rbac->hasPrivilege('add_patient_test_reprt', 'can_delete')) {
            access_denied();
        }
        $this->radio_model->deleteTestReport($id);
    }

    public function radiologyReport() {
        if (!$this->rbac->hasPrivilege('radiology test', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'admin/radio/radiologyreport');
        $select = 'radiology_report.*, radio.id, radio.short_name,staff.name,staff.surname,charges.id as cid,charges.charge_category,charges.standard_charge';
        $join = array(
            'JOIN radio ON radiology_report.radiology_id = radio.id',
            'JOIN staff ON radiology_report.consultant_doctor = staff.id',
            'JOIN charges ON charges.id = radio.charge_id'
        );
        $table_name = "radiology_report";
        $search_type = $this->input->post("search_type");
        if (isset($search_type)) {
            $search_type = $this->input->post("search_type");
        } else {
            $search_type = "this_month";
        }

        if (empty($search_type)) {
            $search_type = "";
            $resultlist = $this->report_model->getReport($select, $join, $table_name);
        } else {

            $search_table = "radiology_report";
            $search_column = "reporting_date";
            $resultlist = $this->report_model->searchReport($select, $join, $table_name, $search_type, $search_table, $search_column);
        }
        $data["searchlist"] = $this->search_type;
        $data["search_type"] = $search_type;
        $data["resultlist"] = $resultlist;
        $this->load->view('layout/header');
        $this->load->view('admin/radio/radiologyReport.php', $data);
        $this->load->view('layout/footer');
    }

}
?>