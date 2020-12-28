<?php

class Sender
{
    public function __construct( $workspace_name )
    {
        $this->workspace_name = $workspace_name;
    }

    public function sendAll()
    {
        $mails = $this->getFiles( $this->getWorkspaceDir() . DIST_DIR );

        foreach( $mails as $mail )
        {
            $mail_contents = file_get_contents( $this->getWorkspaceDir() . DIST_DIR . $mail );
        }
    }

    private function send( $mail )
    {

    }

    private function getFiles( $dir )
    {
        $files = scandir( $this->getWorkspaceDir() . DIST_DIR );

        $files = array_filter( $files, function( $file ){
            return preg_match( '/.html$/', $file );
        });

        return $files;
    }

    private function getWorkspaceDir()
    {
        return WORKSPACE_DIR . $this->workspace_name . '/';
    }
}