<?php

define( 'WORKSPACE_DIR', 'workspace/' );
define( 'COMMONS_DIR', 'common/' );
define( 'DIST_DIR', 'dist/' );

define( 'TEMPLATES_DIR', 'templates/' );

define( 'PARTIALS_DIR', 'partials/' );

class Parser
{
    const PARTIAL_START = '[[-';
    const PARTIAL_END = '-]]';

    const VARIABLE_START = '{{-';
    const VARIABLE_END = '-}}';

    public function __construct( $workspace_name )
    {
        $this->workspace_name = $workspace_name;

        $this->email = $this->getEmailBase();
    }

    public function parse()
    {
        $templates = $this->getTemplates();
        $languages = $this->getLanguages();

        foreach( $languages as $language_name => $language_variables )
        {
            foreach( $templates as $template )
            {
                $template_content = htmlentities( file_get_contents( $this->getWorkspaceDir() . TEMPLATES_DIR . $template ) );

                $template_content = $this->replacePartials( $template_content );
                $template_content = $this->replaceTranslations( $template_content, $language_variables );

                echo html_entity_decode( $template_content );
                echo '<hr>';
            }
        }
    }

    private function getEmailBase()
    {
        $email_base = htmlentities( file_get_contents( COMMONS_DIR . 'base.html' ) );
        return $email_base;
    }

    private function replacePartials( $template_content )
    {
        $partial_start_pos = strpos( $template_content, self::PARTIAL_START, 0 );

        while( $partial_start_pos !== false )
        {
            $partial_end_pos = strpos( $template_content, self::PARTIAL_END, $partial_start_pos );
            $partial_variable_content = substr( $template_content, $partial_start_pos, $partial_end_pos - $partial_start_pos + strlen( self::PARTIAL_END ) );

            $partial_name = $this->getPartialName( $partial_variable_content );
            $partial_content = htmlentities( file_get_contents( $this->getWorkspaceDir() . TEMPLATES_DIR . PARTIALS_DIR . $partial_name ) );
            
            $template_content = str_replace( $partial_variable_content, $partial_content, $template_content );
            
            $partial_start_pos = strpos( $template_content, self::PARTIAL_START, $partial_end_pos );
        }

        return $template_content;
    }

    private function getPartialName( $partial_variable_content )
    {
        $partial_name_start_pos = strpos( $partial_variable_content, ' ', 0 );
        $partial_name_end_pos = strpos( $partial_variable_content, ' ', $partial_name_start_pos + 1 );

        $partial_name = substr( $partial_variable_content, $partial_name_start_pos + 1, $partial_name_end_pos - $partial_name_start_pos - 1 );

        return $partial_name;
    }

    private function replaceTranslations( $template_content, $language_variables )
    {
        $translation_start_pos = strpos( $template_content, self::VARIABLE_START, 0 );

        while( $translation_start_pos !== false )
        {
            $translation_end_pos = strpos( $template_content, self::VARIABLE_END, $translation_start_pos );
            $translation_variable_content = substr( $template_content, $translation_start_pos, $translation_end_pos - $translation_start_pos + strlen( self::PARTIAL_END ) );

            $translation_name = $this->getTranslationName( $translation_variable_content );
            $translation_content = $language_variables[ $translation_name ];
            
            $template_content = str_replace( $translation_variable_content, $translation_content, $template_content );
            
            $translation_start_pos = strpos( $template_content, self::VARIABLE_START, $translation_end_pos );
        }

        return $template_content;
    }

    private function getTranslationName( $translation_variable_content )
    {
        $translation_name_start_pos = strpos( $translation_variable_content, ' ', 0 );
        $translation_name_end_pos = strpos( $translation_variable_content, ' ', $translation_name_start_pos + 1 );

        $translation_name = substr( $translation_variable_content, $translation_name_start_pos + 1, $translation_name_end_pos - $translation_name_start_pos - 1 );

        return $translation_name;
    }

    private function getTemplates()
    {
        return $this->getFiles( TEMPLATES_DIR );
    }

    private function getLanguages()
    {
        return json_decode( file_get_contents( $this->getWorkspaceDir() . 'translations.json' ), true );
    }

    private function getTranslation( $lang )
    {

    }

    private function getFiles( $dir )
    {
        $templates = scandir( $this->getWorkspaceDir() . TEMPLATES_DIR );

        $templates = array_filter( $templates, function( $template ){
            return preg_match( '/.html$/', $template );
        });

        return $templates;
    }

    private function getWorkspaceDir()
    {
        return WORKSPACE_DIR . $this->workspace_name . '/';
    }

   
}