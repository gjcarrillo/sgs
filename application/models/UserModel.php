<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/17/2017
 * Time: 9:48 PM
 */
class UserModel extends CI_Model
{
    public function __construct() {
        parent::__construct();
    }

    public function findIpapediUser($id) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('usuario_docente');
            $this->ipapedi_db->where('cedula', $id);
            $query = $this->ipapedi_db->get();
            if (empty($query->result())) {
                return null;
            } else {
                return $query->result()[0];
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findIpapediAdmin($id) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('admins_ipapedi');
            $this->ipapedi_db->where('nombre', $id);
            $query = $this->ipapedi_db->get();
            if (empty($query->result())) {
                return null;
            } else {
                return $query->result()[0];
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Creates a new user in database.
     *
     * @param $data - new user's data.
     * @throws Exception
     */
    public function createUser($data) {
        try {
            $em = $this->doctrine->em;
            $user = new \Entity\User();
            $user->setId($data['id']);
            $user->setPassword(base64_encode($data['password']));
            $user->setFirstName($data['firstName']);
            $user->setLastname($data['lastName']);
            $user->setType($data['type']);
            $user->setStatus($data['status']);
            $user->setPhone($data['phone']);
            $user->setEmail($data['email']);
            $em->persist($user);
            $em->flush();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function upgradeUser ($uid) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            if ($user->getType() != APPLICANT) {
                // User most be APPLICANT for upgrading to AGENT.
                throw new Exception ('No se pueden otorgar diferentes privilegios a este usuario.');
            } else {
                $user->setType(AGENT);
                $em->merge($user);
                $em->flush();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function degradeUser ($uid) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            if ($user->getType() != AGENT) {
                // User most be AGENT for degrading to APPLICANT
                throw new Exception ('No se puede revocar privilegios a este usuario.');
            } else {
                $user->setType(APPLICANT);
                $em->merge($user);
                $em->flush();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getIpapediUserInfo($uid) {
        $result['message'] = "error";
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_personales');
            $this->ipapedi_db->where('cedula', $uid);
            $query = $this->ipapedi_db->get();
            if (empty($query->result())) {
                return null;
            } else {
                if (empty($query->result())) {
                    throw new Exception("Este usuario no posee datos registrados.");
                } else {
                    $result['data'] = $query->result()[0];
                    $this->ipapedi_db->select('*');
                    $this->ipapedi_db->from('usuario_docente');
                    $this->ipapedi_db->where('cedula', $uid);
                    $query = $this->ipapedi_db->get();
                    // If user data exists, user should be in ipapedi's database so the following should never happen.
                    if (!empty($query->result())) {
                        $result['userName'] = $query->result()[0]->nombre;
                    } else {
                        $em = $this->doctrine->em;
                        $user = $em->find('Entity\User', $uid);
                        $result['userName'] = $user->getFirstName() . " " . $user->getLastName();
                    }
                }
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Resurrects a currently inactive user.
     *
     * @param $uid - corresponding user's id
     * @throws Exception
     */
    public function resurrectUser($uid) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            $user->setStatus("ACTIVO");
            $em->merge($user);
            $em->flush();
        } catch (Exception $e) {
            throw $e;
        }
    }
}