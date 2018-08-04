<?php
namespace app\components;

use app\models\enums\Roles;
/**
 * Description of CronosUser
 *
 * @author twocandles
 */
class CronosUser extends \yii\web\User
{

    /**
     * @param Roles $role
     * @return boolean
     */
    public function hasRole( $role )
    {
        return $this->role == $role;
    }
    
    /**
     * Return Role
     * @return integer
     */
    public function getRole() {
        return $this->identity->role;
    }
    
    /**
     * Check if the role has at least the director role. 
     * @return boolean
     */
    public function hasDirectorPrivileges() {
        return $this->hasRole( Roles::UT_ADMIN ) ||
                $this->hasRole( Roles::UT_DIRECTOR_OP );
    }

    /**
     * Check if the role has commercial privileges
     * @return boolean
     */
    public function hasCommercialPrivileges() {
        return $this->hasRole( Roles::UT_COMERCIAL ) ||
                $this->hasRole( Roles::UT_ADMINISTRATIVE );
    }
    
    /**
     * Retrieve if they have administrative privileges.
     * @return boolean
     */
    public function hasAdministrativePrivileges() {
        return $this->hasRole( Roles::UT_ADMINISTRATIVE );
    }
    
    /**
     * Check if the role has at least the project manager role. 
     * @return boolean
     */
    public function hasProjectManagerPrivileges() {
        return $this->hasRole( Roles::UT_ADMIN ) ||
                $this->hasRole( Roles::UT_DIRECTOR_OP ) ||
                $this->hasRole( Roles::UT_PROJECT_MANAGER );
    }
    
    /**
     * Check if it is project manager.
     * @return bool
     */
    public function isProjectManager() {
        return $this->hasRole( Roles::UT_PROJECT_MANAGER );
    }

    /**
     * Returns if the user is an admin
     */
    public function isAdmin()
    {
        return $this->hasRole( Roles::UT_ADMIN );
    }

}

?>
