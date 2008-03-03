<?php

class eZPESELType extends eZDataType
{
    const DATA_TYPE_STRING = "ezpesel";

    function eZPESELType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezi18n( 'extension/ezpesel', 'PESEL', 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_text' => '' ) ) );
    }

    /*!
     Validates input on content object level
     \return eZInputValidator::STATE_ACCEPTED or eZInputValidator::STATE_INVALID if
             the values are accepted or not
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $data = $http->postVariable( $base . '_pesel_' . $contentObjectAttribute->attribute( "id" ) );
        $data = str_replace(" ", "", $data );

        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        if ( !$contentObjectAttribute->validateIsRequired() and $data == "" )
        {
            return eZInputValidator::STATE_ACCEPTED;
        }

        if ( is_numeric( $data ) )
        {
            $valid = $this->validatePESELChecksum ( $data );

            if ( $valid )
            {
                return eZInputValidator::STATE_ACCEPTED;
            }
            else
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'extension/ezpesel',
                                                                     'The PESEL number is not correct. Please check the input for mistakes.' ) );
                return eZInputValidator::STATE_INVALID;
            }
        }
        else
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'extension/ezpesel',
                                                                 'The PESEL number is not correct. Please check the input for mistakes.' ) );
            return eZInputValidator::STATE_INVALID;
        }
        return eZInputValidator::STATE_INVALID;
    }

    function validatePESELChecksum ( $peselNr )
    {
        if ( strlen( $peselNr ) != 11 )
        {
            return false;
        }

        $arrSteps = array( 1, 3, 7, 9, 1, 3, 7, 9, 1, 3 );
        $intSum = 0;
        for ( $i = 0; $i < 10; $i++ )
        {
            $intSum += $arrSteps[$i] * $peselNr[$i];
        }

        $int = 10 - $intSum % 10;
        $intControlNr = ( $int == 10 ) ? 0 : $int;
        if ( $intControlNr == $peselNr[10] )
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
        $pesel = $http->postVariable( $base . '_pesel_' . $contentObjectAttribute->attribute( "id" ) );
        $contentObjectAttribute->setAttribute( 'data_text', $pesel );
        return true;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     Returns the value as it will be shown if this attribute is used in the object name pattern.
    */
    function title( $objectAttribute, $name = null )
    {
        return $data_instance->attribute( 'data_text' );
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
        return trim( $contentObjectAttribute->attribute( 'data_text' ) ) != '';
    }

}

eZDataType::register( eZPESELType::DATA_TYPE_STRING, "ezpeseltype" );
?>
