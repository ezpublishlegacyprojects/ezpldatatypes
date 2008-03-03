<?php

class eZNIPType extends eZDataType
{
    const DATA_TYPE_STRING = "eznip";

    function eZNIPType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezi18n( 'extension/eznip', 'NIP', 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_text' => 'nip' ) ) );
    }

    /*!
     Validates input on content object level
     \return eZInputValidator::STATE_ACCEPTED or eZInputValidator::STATE_INVALID if
             the values are accepted or not
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $field1 = $http->postVariable( $base . '_nip_field1_' . $contentObjectAttribute->attribute( "id" ) );
        $field2 = $http->postVariable( $base . '_nip_field2_' . $contentObjectAttribute->attribute( "id" ) );
        $field3 = $http->postVariable( $base . '_nip_field3_' . $contentObjectAttribute->attribute( "id" ) );
        $field4 = $http->postVariable( $base . '_nip_field4_' . $contentObjectAttribute->attribute( "id" ) );
        $nip = $field1 . '-' . $field2 . '-' . $field3 . '-' . $field4;

        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        if ( !$contentObjectAttribute->validateIsRequired() and $nip == "---" )
        {
            return eZInputValidator::STATE_ACCEPTED;
        }

         if ( preg_match( "#[0-9]+\-[0-9]+\-[0-9]+\-[0-9]#", $nip ) )
        {
            $digits = str_replace( "-", "", $nip );
            $valid = $this->validateNIPChecksum ( $digits );

            if ( $valid )
            {
                return eZInputValidator::STATE_ACCEPTED;
            }
            else
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'extension/eznip',
                                                                     'The NIP number is not correct. Please check the input for mistakes.' ) );
                return eZInputValidator::STATE_ACCEPTED;
            }
        }
        else
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'extension/eznip',
                                                                 'The NIP number is not correct. Please check the input for mistakes.' ) );
            return eZInputValidator::STATE_INVALID;
        }
        return eZInputValidator::STATE_INVALID;
    }

    function validateNIPChecksum( $nipNr )
    {
        if ( strlen( $nipNr ) != 10 )
        {
            return false;
        }

        $arrSteps = array( 6, 5, 7, 2, 3, 4, 5, 6, 7 );
        $intSum = 0;
        for ( $i = 0; $i < 9; $i++ )
        {
            $intSum += $arrSteps[$i] * $nipNr[$i];
        }
        $int = $intSum % 11;

        $intControlNr = ( $int == 10 ) ? 0 : $int;
        if ( $intControlNr == $nipNr[9] )
        {
            return true;
        }
        return false;
    }

    /*!
     Fetches all variables from the object
     \return true if fetching of class attributes are successfull, false if not
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $field1 = $http->postVariable( $base . '_nip_field1_' . $contentObjectAttribute->attribute( "id" ) );
        $field2 = $http->postVariable( $base . '_nip_field2_' . $contentObjectAttribute->attribute( "id" ) );
        $field3 = $http->postVariable( $base . '_nip_field3_' . $contentObjectAttribute->attribute( "id" ) );
        $field4 = $http->postVariable( $base . '_nip_field4_' . $contentObjectAttribute->attribute( "id" ) );
        $nip = $field1 . '-' . $field2 . '-' . $field3 . '-' . $field4;
        $contentObjectAttribute->setAttribute( 'data_text', $nip );
        return true;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $data = $contentObjectAttribute->attribute( 'data_text' );
        // The array_merge makes sure missing elements gets an empty string instead of NULL
        list ( $field1, $field2, $field3, $field4 ) = array_merge( preg_split( '#-#', $data ),
                                                                   array( 0 => '', 1 => '', 2 => '', 3 => '' ) );
        $nip = array( "field1" => $field1, "field2" => $field2,
                       "field3" => $field3, "field4" => $field4 );
        return $nip;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    /*!
     Returns the value as it will be shown if this attribute is used in the object name pattern.
    */
    function title( $objectAttribute, $name = null )
    {
        return $data_instance->attribute( "data_text" );
    }

    /*!
     \return true if the datatype can be indexed
    */
    function isIndexable()
    {
        return true;
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( "data_text" ) ) != '';
    }

}

eZDataType::register( eZNIPType::DATA_TYPE_STRING, "ezniptype" );
?>
