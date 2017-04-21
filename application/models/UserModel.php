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
            $user->setFirstName(trim($data['firstName']));
            $user->setLastname(trim($data['lastName']));
            $user->setType($data['type']);
            $user->setStatus($data['status']);
            if (isset($data['phone'])) {
                $user->setPhone($data['phone']);
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
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
                // User must be APPLICANT for upgrading to AGENT.
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

    public function updateUserInfo($data) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $data['id']);
            $user->setPassword(base64_encode($data['password']));
            $user->setFirstName(trim($data['firstName']));
            $user->setLastname(trim($data['lastName']));
            if (isset($data['phone'])) {
                $user->setPhone($data['phone']);
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
            $em->merge($user);
            $em->flush();
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

    public function getUser ($uid) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            return $user;
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
                        $result['picture'] = $query->result()[0]->foto;
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


    public function getUserProfileImg($uid) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('usuario_docente');
            $this->ipapedi_db->where('cedula', $uid);
            $query = $this->ipapedi_db->get();
            if (empty($query->result())) {
                return null;
            } else {
                return $query->result()[0]->foto;
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

    /**
     * Gets the system generated user for automated requests history registration purposes.
     *
     * @return mixed - Returns the system user entity.
     * @throws Exception
     */
    public function getSystemGeneratedUser() {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', SID);
            if ($user === null) {
                // Create the system user
                $this->load->model('userModel');
                $this->createUser(
                    array(
                        'id' => SID,
                        'password' => 'GENKEY' . SID . SID . 'KEYGEN',
                        'firstName' => 'Sistema',
                        'lastName' => 'De Gestión de Solicitudes',
                        'type' => APPLICANT,
                        'status' => "INACTIVO"
                    )
                );
                return $em->find('\Entity\User', SID);
            } else {
                return $user;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtains applicant's personal data from ipapedi_db
     * @param $id - user's id.
     * @return array with personal data. null if no data was found.
     */
    public function getPersonalData($id) {
        $this->ipapedi_db = $this->load->database('ipapedi_db', true);
        $this->ipapedi_db->select('*');
        $this->ipapedi_db->from('db_dt_personales');
        $this->ipapedi_db->where('cedula', $id);
        $query = $this->ipapedi_db->get();
        if (empty($query->result())) {
            return null;
        } else {
            return $query->result()[0];
        }
    }

    /**
     * Obtains applicant's contribution data from ipapedi_db
     * @param $id - user's id.
     * @return array with contributuion data. null if no data was found.
     */
    public function getContributionData($id) {
        $this->ipapedi_db = $this->load->database('ipapedi_db', true);
        $this->ipapedi_db->select('*');
        $this->ipapedi_db->from('db_dt_aportes');
        $this->ipapedi_db->where('cedula', $id);
        $query = $this->ipapedi_db->get();
        if (empty($query->result())) {
            return null;
        } else {
            return $query->result()[0];
        }
    }

    /**
     * Determines whether if a specific user is registered.
     *
     * @param $uid = user's id.
     * @return bool - {@code true} if it indeed exists. {@code false} otherwise.
     */
    public function userExists ($uid) {
        $em = $this->doctrine->em;

        $user = $em->find('\Entity\User', $uid);
        return $user !== null;
    }

    /**
     * Calculates the concurrence with the new request's payment fee. This is the
     * sum of the payment fee of all active requests in proportion to the applicant's wage.
     *
     * @param $loans - all applicant's loan info.
     * @param $wage - applicant's wage.
     * @param $newFee - new loan's payment fee.
     * @return float - calculated concurrence percentage.
     */
    public function calculateNewConcurrence($loans, $wage, $newFee) {
        $sum = $newFee;
        foreach ($loans as $loan) {
            // cash vouchers do not count towards user concurrence
            if ($loan->concepto == CASH_VOUCHER) continue;
            if ($loan->saldo_edo > 0) {
                // Active loan. Take into account.
                $sum += intval($loan->otorg_cuota, 10);
            }
        }
        return $sum * 100 / $wage;
    }

    /**
     * Calculates what is the max payment fee of the new request without exceeding 40% concurrence.
     *
     * @param $loans - all applicant's loan info.
     * @param $wage - applicant's wage.
     * @return float - max amount of money user can request without exceeding 40% concurrence.
     */
    public function calculateMaxFeeByConcurrence($loans, $wage) {
        $sum = 0;
        foreach ($loans as $loan) {
            // cash vouchers do not count towards user concurrence
            if ($loan->concepto == CASH_VOUCHER) continue;
            if ($loan->saldo_edo > 0) {
                // Active loan. Take into account.
                $sum += intval($loan->otorg_cuota, 10);
            }
        }
        return 0.4 * $wage - $sum;
    }

    public function calculateMaxTermsByConcurrence($loans, $wage, $reqAmount, $terms, $concept) {
        // Calculate actual concurrence based on other loans payment fees amount
        $sum = 0;
        foreach ($loans as $loan) {
            // cash vouchers do not count towards user concurrence
            if ($loan->concepto == CASH_VOUCHER) continue;
            if ($loan->saldo_edo > 0) {
                // Active loan. Take into account.
                $sum += intval($loan->otorg_cuota, 10);
            }
        }
        // Check new concurrence with each term.
        $loanTypes = $this->configModel->getLoanTypes();
        asort($terms);
        foreach ($terms as $term) {
            $newFee = $this->utils->calculatePaymentFee($reqAmount, $term, $loanTypes[$concept]->InteresAnual);
            $newConcurrence = ($sum + $newFee) * 100 / $wage;
            if ($newConcurrence <= 40) {
                return $term;
            }
        }
        return 'No disponible';
    }

    public function isReqAmountValid($reqAmount, $concept, $userData) {
        switch (intval($concept, 10)) {
            case CASH_VOUCHER:
                $percentage = $this->configModel->getCashVoucherPercentage();
                $maxAmount = $userData->sueldo * $percentage / 100;
                return $reqAmount > 0 && $reqAmount <= $maxAmount;
            case PERSONAL_LOAN:
                $maxAmount = $userData->u_saldo_disp + $userData->p_saldo_disp;
                return $reqAmount > 0 && $reqAmount <= $maxAmount;
            default:
                return false;
        }
    }
}