<?php

class eZREGONType extends eZDataType
{
    const DATA_TYPE_STRING = "ezregon";

    function eZREGONType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezi18n( 'extension/ezregon', 'REGON', 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_int' => 'value' ) ) );
    }

    /*!
     Validates input on content object level
     \return eZInputValidator::STATE_ACCEPTED or eZInputValidator::STATE_INVALID if
             the values are accepted or not
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $data = $http->postVariable( $base . '_regon_' . $contentObjectAttribute->attribute( "id" ) );
        $data = str_replace(" ", "", $data );

        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        if ( !$contentObjectAttribute->validateIsRequired() and $data == "" )
        {
            return eZInputValidator::STATE_ACCEPTED;
        }

        if ( is_numeric( $data ) )
        {
            $valid = $this->validateREGONChecksum ( $data );

            if ( $valid )
            {
                return eZInputValidator::STATE_ACCEPTED;
            }
            else
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'extension/ezregon',
                                                                     'The REGON number is not correct. Please check the input for mistakes.' ) );
                return eZInputValidator::STATE_INVALID;
            }
        }
        else
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'extension/ezregon',
                                                                 'The REGON number is not correct. Please check the input for mistakes.' ) );
            return eZInputValidator::STATE_INVALID;
        }
        return eZInputValidator::STATE_INVALID;
    }

    function validateREGONChecksum ( $regonNr )
    {
        if ( strlen( $regonNr ) != 9 )
        {
            return false;
        }

        $arrSteps = array( 8, 9, 2, 3, 4, 5, 6, 7 );
        $intSum = 0;
        for ( $i = 0; $i < 8; $i++ )
        {
            $intSum += $arrSteps[$i] * $regonNr[$i];
        }
        $int = $intSum % 11;
        $intControlNr = ( $int == 10 ) ? 0 : $int;
        if ( $intControlNr == $regonNr[8] )
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
        $regon = $http->postVariable( $base . '_regon_' . $contentObjectAttribute->attribute( "id" ) );
        $contentObjectAttribute->setAttribute( 'data_int', $regon );
        return true;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_int' );
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_int' );
    }

    /*!
     Returns the value as it will be shown if this attribute is used in the object name pattern.
    */
    function title( $objectAttribute, $name = null )
    {
        return $data_instance->attribute( 'data_int' );
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
        return trim( $contentObjectAttribute->attribute( 'data_int' ) ) != '';
    }

}

eZDataType::register( eZREGONType::DATA_TYPE_STRING, "ezregontype" );
?>
