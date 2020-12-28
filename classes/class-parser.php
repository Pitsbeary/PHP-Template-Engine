<?php

class Parser
{
    const PARTIAL_START = '[[-';
    const PARTIAL_END = '-]]';

    const BODY_TAG = self::PARTIAL_START . 'BODY' . self::PARTIAL_END;

    const VARIABLE_START = '{{-';
    const VARIABLE_END = '-}}';

    public function __construct( $workspace_name )
    {
        $this->workspace_name = $workspace_name;

        $this->email = $this->getEmailBase();
    }

    public function parse()
    {
        $template_base = $this->getTemplateBase();

        $templates = $this->getTemplates();
        $templates = array_diff( $templates, array( 'base.html' ) );

        $languages = $this->getLanguages();

        $dist_dir_path = $this->getWorkspaceDir() . DIST_DIR;

        if (!file_exists( $dist_dir_path ) )
        {
            mkdir( $dist_dir_path );
        }

        foreach( $languages as $language_name => $language_variables )
        {
            $dist_dir_path_lang = $dist_dir_path . $language_name . '/';

            global $current_lang_variables;
            $current_lang_variables = $language_variables; // REFACTOR LATER - MAKE IT A PART OF GLOBAL CONTEXT (?)

            if( !file_exists( $dist_dir_path_lang ) )
            {
                mkdir( $dist_dir_path_lang );
            }

            foreach( $templates as $template )
            {
                $template_current_base = $template_base;

                $template_content = htmlentities( file_get_contents( $this->getWorkspaceDir() . TEMPLATES_DIR . $template ) );

                $template_content = $this->replacePartials( $template_content );
                $template_content = $this->replaceTranslations( $template_content );

                $template_content = str_replace( self::BODY_TAG, $template_content, $template_current_base );

                file_put_contents( $dist_dir_path_lang . $template, html_entity_decode( $template_content ) );
                echo html_entity_decode( $template_content );
                echo '<hr>';
            }
        }
    }

    private function getTemplateBase()
    {
        $template_base = null;

        if( file_exists( $this->getWorkspaceDir() . TEMPLATES_DIR . 'base.html' ) )
        {
            $template_base = htmlentities( file_get_contents( $this->getWorkspaceDir() . TEMPLATES_DIR . 'base.html' ) ); 
        }
        else
        {
            $template_base = htmlentities( file_get_contents( COMMONS_DIR . 'base.html' ) ); 
        }

        return $template_base;
    }

    private function getEmailBase()
    {
        $email_base = htmlentities( file_get_contents( COMMONS_DIR . 'base.html' ) );
        return $email_base;
    }

    private function replacePartials( $template_content )
    {
        return $this->replaceVariables( $template_content, self::PARTIAL_START, self::PARTIAL_END, 'replacePartial' );
    }

    private function replacePartial( $template_content, $partial_variable_content )
    {
        $partial_name = $this->readVariable( $partial_variable_content );
        $partial_content = htmlentities( file_get_contents( $this->getWorkspaceDir() . TEMPLATES_DIR . PARTIALS_DIR . $partial_name ) );

        $template_content = str_replace( $partial_variable_content, $partial_content, $template_content );
        return $template_content;
    }

    private function replaceTranslations( $template_content )
    {
        return $this->replaceVariables( $template_content, self::VARIABLE_START, self::VARIABLE_END, 'replaceTranslation' );
    }

    private function replaceTranslation( $template_content, $translation_variable_content )
    {
        global $current_lang_variables;

        $translation_name = $this->readVariable( $translation_variable_content );
        $translation_content = $current_lang_variables[ $translation_name ];

        $template_content = str_replace( $translation_variable_content, $translation_content, $template_content );
        return $template_content;
    }

    private function getTemplates()
    {
        return $this->getFiles( TEMPLATES_DIR );
    }

    private function getLanguages()
    {
        return json_decode( file_get_contents( $this->getWorkspaceDir() . 'translations.json' ), true );
    }

    private function getFiles( $dir )
    {
        $files = scandir( $this->getWorkspaceDir() . TEMPLATES_DIR );

        $files = array_filter( $files, function( $file ){
            return preg_match( '/.html$/', $file );
        });

        return $files;
    }

    private function getWorkspaceDir()
    {
        return WORKSPACE_DIR . $this->workspace_name . '/';
    }

    private function replaceVariables( $template_content, $variable_start, $variable_end, $replace_callback )
    {
        $variable_start_pos = strpos( $template_content, $variable_start, 0 );

        while( $variable_start_pos !== false )
        {
            $variable_end_pos = strpos( $template_content, $variable_end, $variable_start_pos );
            $variable_content = substr( $template_content, $variable_start_pos, $variable_end_pos - $variable_start_pos + strlen( $variable_end ) );
       
            $template_content = call_user_func_array( array( $this, $replace_callback ), array( $template_content, $variable_content ) ); // REFACTOR LATER
            
            $variable_start_pos = strpos( $template_content, $variable_start, $variable_end_pos );
        }

        return $template_content; 
    }

    private function readVariable( $variable_content )
    {
        $variable_name_start_pos = strpos( $variable_content, ' ', 0 );
        $variable_name_end_pos = strpos( $variable_content, ' ', $variable_name_start_pos + 1 );

        $variable_name = substr( $variable_content, $variable_name_start_pos + 1, $variable_name_end_pos - $variable_name_start_pos - 1 );

        return $variable_name;
    }

}

// THINK ABOUT: GLOBAL CONTEXT FOR TRANSLATIONS
// THINK ABOUT: WORKFLOW (?)