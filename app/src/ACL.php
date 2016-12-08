<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Access Level Control
 *  
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class ACL
{

    /**
     * Stores the permissions for the user
     *
     * @access public
     * @var array
     */
    protected $_perms = [];

    /**
     * Stores the ID of the current user
     *
     * @access public
     * @var integer
     */
    protected $_id = 0;

    /**
     * Stores the roles of the current user
     *
     * @access public
     * @var array
     */
    protected $_userRoles = [];
    
    public $app;

    public function __construct($id = '')
    {
        $this->app = \Liten\Liten::getInstance();

        if ($id != '') {
            $this->_id = floatval($id);
        } else {
            $this->_id = floatval(get_userdata('id'));
        }
        $this->_userRoles = $this->getUserRoles('ids');
        $this->buildACL();
    }

    public function ACL($id = '')
    {
        $this->__construct($id);
    }

    public function buildACL()
    {
        //first, get the rules for the user's role
		if (count($this->_userRoles) > 0) {
			$this->_perms = array_merge($this->_perms,$this->getRolePerms($this->_userRoles));
		}
		//then, get the individual user permissions
		$this->_perms = array_merge($this->_perms,$this->getUserPerms($this->_id));
        
    }

    public function getPermKeyFromID($permID)
    {
        $strSQL = $this->app->db->permission()
            ->select('permission.permKey')
            ->where('id = ?', floatval($permID))
            ->limit(1);
        $q = $strSQL->find(function($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        foreach($q as $r) {
            return $r['permKey'];
        }
    }

    public function getPermNameFromID($permID)
    {
        $strSQL = $this->app->db->permission()
            ->select('permission.permName')
            ->where('id = ?', floatval($permID))
            ->limit(1);
        $q = $strSQL->find(function($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        foreach($q as $r) {
            return $r['permName'];
        }
    }

    public function getRoleNameFromID($roleID)
    {
        $strSQL = $this->app->db->role()
            ->select('role.roleName')
            ->where('id = ?', floatval($roleID))
            ->findOne();
        
            return $strSQL->roleName;
    }

    public function getUserRoles()
    {
        $strSQL = $this->app->db->user()
            ->where('id = ?', floatval($this->_id))
            ->orderBy('date_added', 'ASC')
            ->find();
        
        $resp = [];
        foreach($strSQL as $r) {
            $resp[] = $r->roleID;
        }
        
        return $resp;
    }

    public function getAllRoles($format = 'ids')
    {
        $format = strtolower($format);

        $strSQL = $this->app->db->role()
            ->orderBy('roleName', 'ASC');
        $q = $strSQL->find(function($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        $resp = [];
        foreach($q as $r) {
            if ($format == 'full')
			{
				$resp[] = [ "ID" => $r['id'],"Name" => $r['roleName'] ];
			} else {
				$resp[] = $r['id'];
			}
        }
        return $resp;
    }

    public function getAllPerms($format = 'ids')
    {
        $format = strtolower($format);

        $strSQL = $this->app->db->permission()
            ->orderBy('permName', 'ASC');
        $q = $strSQL->find(function($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        $resp = [];
        foreach($q as $r) {
            if ($format == 'full') {
				$resp[$r['permKey']] = [ 'ID' => $r['id'], 'Name' => $r['permName'], 'Key' => $r['permKey'] ];
			} else {
				$resp[] = $r['id'];
			}
        }
        return $resp;
    }

    public function getRolePerms($role)
    {
        if (is_array($role)) {
            $roleSQL = $this->app->db->query("SELECT * FROM role_perms WHERE roleID IN (" . implode(",",$role) . ") ORDER BY id ASC");
        } else {
            $roleSQL = $this->app->db->role_perms()
                ->where('roleID = ?', floatval($role))
                ->orderBy('id', 'ASC');
        }

       $q = $roleSQL->find(function($data) {
           $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        $perms = [];
        foreach($q as $r) {
            $pK = strtolower($this->getPermKeyFromID($r['permID']));
			if ($pK == '') { continue; }
			if ($r['value'] === '1') {
				$hP = true;
			} else {
				$hP = false;
			}
			$perms[$pK] = [ 'perm' => $pK,'inheritted' => true,'value' => $hP,'Name' => $this->getPermNameFromID($r['permID']),'ID' => $r['permID'] ];
        }
        return $perms;
    }

    public function getUserPerms($id)
    {
        $strSQL = $this->app->db->user_perms()
            ->where('id = ?', floatval($id))
            ->orderBy('LastUpdate', 'ASC');

        $q = $strSQL->find(function($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        $perms = [];
        foreach ($q as $r) {
                $pK = strtolower($this->getPermKeyFromID($r['permID']));
                if ($pK == '') {
                    continue;
                }
                if ($r['value'] === '1') {
                    $hP = true;
                } else {
                    $hP = false;
                }
                $perms[$pK] = [ 'perm' => $pK, 'inheritted' => false, 'value' => $hP, 'Name' => $this->getPermNameFromID($r['permID']), 'ID' => $r['permID'] ];
            }
            return $perms;
    }

    public function userHasRole($roleID)
    {
        foreach ($this->_userRoles as $k => $v) {
            if (floatval($v) === floatval($roleID)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission($permKey)
    {
        $roles = $this->app->db->query("SELECT 
						a.id 
					FROM 
						role a 
					LEFT JOIN 
						user b 
					ON 
						a.id = b.roleID
					WHERE 
						a.permission LIKE ? 
					AND 
						b.id = ?", ["%$permKey%", get_userdata('id')]
        );
        $q1 = $roles->find(function($data) {
            $array = [];
            foreach($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        $perms = $this->app->db->query('SELECT id FROM user_perms WHERE permission LIKE ? AND id = ?', ["%$permKey%", get_userdata('id')]);
        
        $q2 = $perms->find(function($data) {
            $array = [];
            foreach($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        
        if (count($q1) > 0) {
            return true;
        } elseif (count($q2) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUsername($id)
    {
        $strSQL = $this->app->db->user()
            ->select('user.uname')
            ->where('id = ?', floatval($id))
            ->limit(1);
        $q = $strSQL->find(function($data) {
            foreach ($data as $d) {
                return $d['uname'];
            }
        });
        
        $array = [];
        foreach($q as $r) {
            $array[] = $r;
        }
        return $array;
    }
}
