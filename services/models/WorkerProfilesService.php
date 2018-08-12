<?php
namespace app\services\models;

use app\services\CronosService;
use app\models\db\Project;
use app\models\db\WorkerProfile;
use app\models\db\PricePerProjectAndProfile;

/**
 * Description of WorkerProfilesService
 *
 * @author twocandles
 */
class WorkerProfilesService implements CronosService
{
    const MY_LOG_CATEGORY = 'services.WorkerProfilesService';

    /**
     * Retrieves an array of PricePerProjectAndProfile models for the project
     * If it doesn't exist then retrieves the default profiles prices
     * @return array
     */
    public function getArrayOfPricePerProjectAndProfileModelsFromProject( $project_id )
    {
        // Depending on whether looking for a specific project or
        // the default values for all the projects
        $isValidId = Project::isValidID( $project_id );
        $data = array();
        // If valid ID, try to retrieve values for project
        if( $isValidId )
        {
            $data = PricePerProjectAndProfile::findAll(
                            'project_id=:projid',
                            array( 'projid' => $project_id ) );
            $result = array( );
            $idField = 'worker_profile_id';
            $valField = 'price';
        }
        // If invalid ID or not data found before, get the values from
        // defaults
        if( (!$isValidId ) || ( count( $data ) == 0 ) )
        {
            $defaults = WorkerProfile::find()->all();
            foreach( $defaults as $row )
            {
                $newPrice = new PricePerProjectAndProfile;
                $newPrice->worker_profile_id = $row['id'];
                $newPrice->price = $row['dflt_price'];
                $data[] = $newPrice;
            }
        }
        return $data;
    }

    /**
     *
     * @param <type> $profileId
     */
    public function getMapOfProfilePricesForProject( $projectId )
    {
        // TODO: implement a cache policy
        $data = $this->getArrayOfPricePerProjectAndProfileModelsFromProject($projectId);
        $result = array();
        foreach( $data as $ppp )
        {
            assert( $ppp instanceof PricePerProjectAndProfile );
            $result[$ppp->worker_profile_id] = $ppp->price;
        }
        return $result;
    }

    public function getDefaultArrayOfPricePerProjectAndProfileModels()
    {
        return $this->getArrayOfPricePerProjectAndProfileModelsFromProject( null );
    }

    private function internalSaveProfilesToProject( $profiles, $project )
    {
        // First delete all values
        PricePerProjectAndProfile::find()->where( array( 'project_id' => $project ) )->delete();
        // Insert the profiles
        foreach( $profiles as $workerProfileModel )
        {
            assert( $workerProfileModel instanceof PricePerProjectAndProfile );
            $workerProfileModel->project_id = $project;
            // Try to save. If can't save, abort
            if( !$workerProfileModel->save() )
                return false;
        }
        return true;
    }

    public function validateArrayOfProfiles( $profilesArray, $model, $att )
    {
        if( count( $profilesArray ) != count( WorkerProfiles::getValidValues() ) )
        {
            Yii::log( "Invalid number of profiles", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY );
            $model->addError( $att, 'Numero incorrecto de perfiles' );
            return false;
        }
        foreach( $profilesArray as $pricePerProjectAndProfileModel )
        {
            $pricePerProjectAndProfileModel->validate();
            foreach( $pricePerProjectAndProfileModel->getErrors() as $error )
                $model->addError( $att, $error );
        }
        return $model->hasErrors();
    }

    /**
     * Saves a relation of project-profile-price.
     * $profiles is an array specified as
     * $profileId => $profilePrice BOTH STRINGS!
     * @param <type> $profiles
     * @param <type> $project
     * */
    public function saveProfilePricesForProject( $profiles, $project )
    {
        return $this->internalSaveProfilesToProject( $profiles, $project );
    }

}

?>
