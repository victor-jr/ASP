<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logs_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ADD NEW CHECKIN
    public function add_new_lab_log($data) {
        $this->db->trans_start();

        $insertData = array();

        // GET STUDENT ID 
        if(isset($data['studentId'])) { 
        $this->db->select('id');
        $this->db->where('studNo', $data['studentId']);
        $query = $this->db->get('students');
        $student = $query->row_array();
        $insertData['StudentId'] = $student['id'];
        }
        // GET PURPOSE ID
        $this->db->select('id');
        $this->db->where('purpose', $data['purpose']);
        $query = $this->db->get('purposes');
        $purpose = $query->row_array();
        $insertData['PurposeId'] = $purpose['id'];

        // IF NOT STUDENT GET THE USERTYPE AND NAME
        if (isset($data['user']) && isset($data['userName'])) {
            $insertData['userType'] = $data['user'];
            $insertData['name'] = $data['userName'];
        }

        // INSERT REST OF DATA
        $insertData['purposeDetail'] = $data['purposeDetail'];
        $insertData['LabId'] = $data['labId'];
        $insertData['CourseId'] = $data['courseId'];

        // GET DEFAULT TIMEZONE AND CREATE A TIMESTAMP
        date_default_timezone_set('Pacific/Majuro');
        $insertData['checkIn'] = date('Y-m-d H:i');

        $this->db->insert('lab_logs', $insertData);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function checkout_user($id) {
        date_default_timezone_set('Pacific/Majuro');
        $now = date('Y-m-d H:i');

        $this->db->where('id', $id);
        $this->db->set('checkOut', $now);
        $query = $this->db->update('lab_logs');
        print_r($this->db->affected_rows());
    }

    public function get_all_logs() {
        $this->db->select('ll.id,l.name as labName,ll.checkIn,ll.checkOut,ll.userType,ll.name,s.studNo,s.name as studentName,c.course,p.purpose');
        $this->db->order_by('ll.checkIn', 'DESC');
        $this->db->join('labs l', 'l.id = ll.LabId', 'left');
        $this->db->join('students s', 's.id = ll.StudentId', 'left');
        $this->db->join('courses c', 'c.id = ll.CourseId', 'left');
        $this->db->join('purposes p', 'p.id = ll.PurposeId', 'left');
        $result = $this->db->get('lab_logs ll');

        return $result->result_array();
    
    }    

    public function get_all_logs_where($data) {
        $this->db->select('ll.id,l.name as labName,ll.checkIn,ll.checkOut,ll.userType,ll.name,s.studNo,s.name as studentName,c.course,p.purpose');
        $this->db->order_by('ll.checkIn', 'DESC');
        $this->db->join('labs l', 'l.id = ll.LabId', 'left');
        $this->db->join('students s', 's.id = ll.StudentId', 'left');
        $this->db->join('courses c', 'c.id = ll.CourseId', 'left');
        $this->db->join('purposes p', 'p.id = ll.PurposeId', 'left');
        if($data['lab']!=='') {
            $this->db->where('ll.LabId', $data['lab']);
        }
        if($data['studNo']!=='') { 
            $this->db->where('s.studNo', $data['studNo']);
        }            
        
        $result = $this->db->get('lab_logs ll');
    
        return $result->result_array();

    } 

    public function get_log($logId) {
        $this->db->select('ll.id,l.name as labName,ll.checkIn,ll.checkOut,ll.userType,ll.name,s.studNo,s.name as studentName,c.course,p.purpose,ll.purposeDetail');
        $this->db->order_by('ll.checkIn', 'DESC');
        $this->db->join('labs l', 'l.id = ll.LabId', 'left');
        $this->db->join('students s', 's.id = ll.StudentId', 'left');
        $this->db->join('courses c', 'c.id = ll.CourseId', 'left');
        $this->db->join('purposes p', 'p.id = ll.PurposeId', 'left');
        $this->db->where('ll.id', $logId);         
        
        $result = $this->db->get('lab_logs ll');
    
        return $result->row_array();
    } 

    public function update_log($data) {
        $insertData = array();

        // GET STUDENT ID
        $this->db->select('id');
        $this->db->where('studNo', $data['studNo']);
        $query = $this->db->get('students');
        $student = $query->row_array();
        $insertData['StudentId'] = $student['id'];

        // GET PURPOSE ID
        $this->db->select('id');
        $this->db->where('purpose', $data['purpose']);
        $query = $this->db->get('purposes');
        $purpose = $query->row_array();
        $insertData['PurposeId'] = $purpose['id'];

        // GET LAB ID
        $this->db->select('id');
        $this->db->where('name', $data['lab']);
        $query = $this->db->get('labs');
        $lab = $query->row_array();
        $insertData['LabId'] = $lab['id'];

        // PREP REST OF INSERT DATA
        $insertData['checkIn'] = $data['checkIn'];
        $insertData['checkOut'] = $data['checkOut'];
        $insertData['userType'] = $data['userType'];
        $insertData['name'] = $data['name'];
        $insertData['purposeDetail'] = $data['purposeDetail'];
        $insertData['CourseId'] = $data['courseId'];

        $this->db->where('id', $data['id']);
        return $this->db->update('lab_logs',$insertData);
    }

    public function upload_logs($data) {
        $insertData = array();
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        foreach ($data as $key => $value) {
            // FIND ID FOR STUDENT
            $this->db->select('id');
            $this->db->where('studNo', $value[5]);
            $query = $this->db->get('students');
            $student = $query->row_array();
            $StudentId = $student['id'];

            // GET COURSE ID
            $this->db->select('id');
            $this->db->where('course', $value[6]);
            $query = $this->db->get('courses');
            $course = $query->row_array();
            $CourseId = $course['id'];

            // GET PURPOSE ID
            $this->db->select('id');
            $this->db->where('purpose', $value[7]);
            $query = $this->db->get('purposes');
            $purpose = $query->row_array();
            $PurposeId = $purpose['id'];

            // GET LAB ID
            $this->db->select('id');
            $this->db->where('name', $value[8]);
            $query = $this->db->get('labs');
            $lab = $query->row_array();
            $LabId = $lab['id'];

            // ADD DATA TO DB FIELDS FOR INSERT
            $insertData[$key] = array(
                'checkIn' => date_format(date_create($value[0]),'Y-m-d H:i'),
                'checkOut' => date_format(date_create($value[1]),'Y-m-d H:i'),
                'userType' => $value[2],
                'name' => $value[3],
                'purposeDetail' => $value[4],
                'StudentId' => $StudentId,
                'CourseId' => $CourseId,
                'PurposeId' => $PurposeId,
                'LabId' => $LabId
            );

        }


        if ($insertData[0]['LabId']=='') {
            return FALSE;
        } else {
            foreach ($insertData as $value) {
                $this->db->insert('lab_logs', $value);
            }
            return TRUE;
        }
        
        
        echo "<pre>";
        print_r($insertData);
        echo "</pre>";
    }  

}