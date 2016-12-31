<?php

require_once('/usr/share/php/CAS.php');
require_once('/srv/www/php/dbconnector.php');

class CasNEIISTAuthBackend extends ExternalStaffAuthenticationBackend {
    static $id = "cas";
    static $name = "NEIIST CAS";

    static $service_name = "IST ID";

    function __construct() {
        phpCAS::client(CAS_VERSION_3_0,'id.tecnico.ulisboa.pt',443,'/cas');
        phpCAS::setCasServerCACert('/etc/ssl/certs/AddTrust_External_Root.pem');
        phpCAS::handleLogoutRequests(true, array('id.tecnico.ulisboa.pt'));
    }

    function getName() {
        return self::$name;
    }

    function signOn() {
        if(phpCAS::isAuthenticated()) {
            $username = phpCas::getUser();
            error_log($username);
            // osTicket >= v1.10
            $staff = StaffSession::lookup($username);
            if (!$staff instanceof StaffSession) {
                // osTicket <= v1.9.7 or so
                $staff = new StaffSession($username);
            }
            if ($staff && $staff->getId()) {
                $db = neiist_db_connect("osticket-user", "neiist_people");
                if($db === -1 || $db === -2) die("Bad NEIIST DB user configuration");
                $sql = "SELECT person_oid FROM RegularAssociate WHERE ist_id LIKE '" . $username . "'";
                $result = $db->query($sql);
                if($result === FALSE) die($db->error);
                if($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $oid = $row['person_oid'];
                    $sql = "SELECT email FROM Entity WHERE oid = '" . $oid . "'";
                    $result = $db->query($sql);
                    if($result === FALSE) die($db->error);
                    $row = $result->fetch_assoc();
                    $email = $row['email'];

                    if($staff->getEmail() !== $email) {
                        $errors = array();
                        $userdata = $staff->getHashtable();
                        $newdata = array(
                            'id' => $staff->getId(),
                            'firstname' => $userdata['firstname'],
                            'lastname' => $userdata['lastname'],
                            'email' => $email,
                            'phone' => $userdata['phone'],
                            'phone_ext' => $userdata['phone_ext'],
                            'mobile' => $userdata['mobile'],
                            'signature' => $userdata['signature'],
                            'timezone_id' => $userdata['timezone_id'],
                            'daylight_saving' => $userdata['daylight_saving'],
                            'show_assigned_tickets' => $userdata['show_assigned_tickets'],
                            'max_page_size' => $userdata['max_page_size'],
                            'auto_refresh_rate' => $userdata['auto_refresh_rate'],
                            'default_signature_type' => $userdata['default_signature_type'],
                            'default_paper_size' => $userdata['default_paper_size'],
                            'lang' => $staff->getLanguage()
                        );
                        $newdata['email'] = $email;
                        $staff->updateProfile($newdata, $errors);
                    }
                    return $staff;
                } else {
                    $_SESSION['_staff']['auth']['msg'] = 'Error getting user "' . $username . '" from database';
                }
            } else {
                $_SESSION['_staff']['auth']['msg'] = 'Have your administrator create a local account';
            }
        } else {
            Http::redirect(ROOT_PATH . 'scp/login.php?do=ext&bk=cas');
        }
    }

    static function signOut($user) {
        parent::signOut($user);
        phpCAS::logout();
    }

    function triggerAuth() {
        parent::triggerAuth();
        if(!phpCAS::isAuthenticated()) phpCAS::forceAuthentication();
        Http::redirect(ROOT_PATH . 'scp/');
    }
}

