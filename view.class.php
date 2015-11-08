<?php

class View
{
    private $page;
    private $error;
    private $rec_include = 0; // 2 levels of recursion
    private $rec_replace = 0; // 2 levels of recursion
    
    public function __construct( $template = "index" ) 
    {   	
        if ( file_exists( __VIEW . "/" . $template . ".txt" ) )
        {                       
            $this->page = join( "" , file( __VIEW . "/" . $template . ".txt" ) );
            $this->applyIncludes();
        }
        else
        {
            $this->error = "views file $template not found.";
        }
    }   
	
    public function process( $tags = array() ) 
    {       	
        if ( sizeof( $tags ) > 0 )
        {
            foreach ( $tags as $tag => $data ) 
            {
                if( is_array( $data ) )
                {
                    $this->processDirective( $data[0] , $data[1] , $data[2] );
                    unset( $tags[ $tag ] );
                    $this->process( $tags );
                    return;
                }
                else
				{
					$this->page = preg_replace( "/{" . $tag . "}/" , $data , $this->page );
                }
            }
        }
        
        if( preg_match_all( "/\\{[a-zA-Z0-9_\\.]+\\}/" , $this->page , $matches ) > 0 )
        {
            if( $this->rec_replace < 2 )
            {
                $this->rec_replace++;
                $this->process( $tags );
            } 
    	    else
    	    {
                 preg_replace("/\\{[a-zA-Z0-9_\\.]+\\}/", "", $this->page); // cant find (so replace with nothing)
            }
        }
    }

    private function processDirective( $match , $class , $callback )
    {
        $pattern = "/\#$match\#(.*)\#\/$match\#/s";
        $num = preg_match( $pattern , $this->page , $matches );
        if( $num > 0 )
        {
            $replacement = call_user_func_array( array( $class , $callback ) , array( $matches[1] ) );

            $this->page = preg_replace( $pattern , $replacement , $this->page );
        }
    }
	
    public function output() 
    {
        echo $this->page;
    }

    private function loadFile( $file ) 
    {
        ob_start();
        include( __VIEW . "/" . $file );
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    } 

    private function applyIncludes()
    {
        preg_match_all( "/\\@[a-zA-Z0-9_\\.]+\\@/" , $this->page , $matches );

        foreach( $matches as $match )
        {
            for ( $i = 0; $i < count( $match ); $i++ ) 
            {
                $include_name = substr( $match[$i] , 1 , strlen( $match[$i] ) - 2 );
                $data = $this->loadFile( $include_name . ".txt" );
                $this->page = preg_replace( "/@" . $include_name . "@/" , $data , $this->page );         
            }
        }
        
        if( preg_match_all( "/\\@[a-zA-Z0-9_\\.]+\\@/" , $this->page , $matches ) > 0 )
        {
            if( $this->rec_include < 2 )
            {
                $this->rec_include++;
                $this->applyIncludes();
            }             
        }
    }

}

