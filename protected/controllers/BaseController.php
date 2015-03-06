<?php

class BaseController extends Controller
{
    /**
     * Important function for enforcing SSL on a page.
     * Mainly used for the Login page.
     */
    protected function makeSSL()
    {
        if($_SERVER['SERVER_PORT'] != 443) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            exit();
        }
    }

    /**
     * Forces page to not be SSL.
     */
    protected function makeNonSSL()
    {
        if($_SERVER['SERVER_PORT'] == 443) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            exit();
        }
    }
    
    /**
     * Checks to see if a user is logged into the application.
     * If not then it will redirect to the login page with a warning.
     */
    protected function noGuest()
    {
        if(Yii::app()->user->isGuest) {
            Yii::app()->user->setFlash("warning","You must be signed in to access this page.");
            $this->redirect(Yii::app()->createUrl('login')."?redirect=".urlencode("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
            exit;
        }
    }

    private function has_required($request, $required)
    {
        $keys = array_keys($request);
        return (count(array_intersect($required, $keys)) == count($required));
    }
    
    /**
     * Restricts pages from view if under a certain permission level.
     * Will not show a warning by default
     * 
     * @param   (int)       $level          This is the level the user must at least be in order to not be redirected away
     * @param   (boolean)   $show_warning   Whether to show unauthorized access or not (default = false)
     */
    protected function restrictPermission($level,$show_warning=FALSE) {
        try {
            # No Guests allowed
            if(Yii::app()->user->isGuest) {
                throw new Exception("1");
            }
            # Load user, check permission level
            $user = new UserObj(Yii::app()->user->name);
            if(!$user->loaded or !isset($user->permission) or $user->permission < $level) {
                throw new Exception("2");
            }
        }
        # Got to here? User does not have enough permission to view page, to be redirected
        catch (Exception $e) {
            if($show_warning === TRUE) {
                Yii::app()->user->setFlash("warning","You do not have proper permissions to view that page. (".$e->getMessage().")");
            }
            $this->redirect(Yii::app()->baseUrl);
            exit;
        }
        
        # No further action required
    }
    
    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }
}
