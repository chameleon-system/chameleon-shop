<?php

/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License .
 * *****************************************************************************
 */




/**
 * OpenSSL Implemntation of the verify signature algorithm
 *
 */
class OpenSslVerifySignature implements VerifySignature
{
    private $defaultHostPattern = '/^sns\.[a-zA-Z0-9\-]{3,}\.amazonaws\.com(\.cn)?$/';
    /**
     * Create a new instance of the openssl implementation of
     * verify signature
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Verify that the signature is correct for the given data and
     * public key
     *
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificatePath path to certificate, can be file or url
     *
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if there
     *                                                                is an error
     *                                                                with the call
     *
     * @return bool true if valid
     */
    public function verifySignatureIsCorrect($data, $signature, $certificatePath)
    {
        $cert = $this->_getCertificateFromCertifcatePath($certificatePath);

        $certKey = openssl_get_publickey($cert);

        if ($certKey === False) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Unable to extract public key from cert " . $cert);
        }

        $result = -1;
        try {
            $result = openssl_verify($data, $signature, $certKey, OPENSSL_ALGO_SHA1);
        } catch (Exception $ex) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Unable to verify signature - error with the verification algorithm",
                null, $ex
            );
        }

        return ($result > 0);
    }

    /**
     * Request the signing certificate from the given path, in order to
     * get the public key
     *
     * @param string $certificatePath certificate path to retreive
     *
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     *
     * @return void
     */
    private function _getCertificateFromCertifcatePath($certificatePath)
    {
        $this->_validateUrl($certificatePath); //ADDED EXTRA CHECK
        try {
            $cert = file_get_contents($certificatePath);
        } catch (Exception $ex) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with signature validation - unable to request signing ".
                "certificate at " . $certificatePath, null, $ex
            );
        }

        if ($cert === false) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with signature validation - unable to request signing ".
                "certificate at " . $certificatePath
            );
        }

        return $cert;
    }

    /* Ensures that the URL of the certificate is one belonging to AWS, and not
     * just something from the amazonaws domain, which could include S3 buckets.
    *
    * @param string $url Certificate URL
    *
    * @throws InvalidSnsMessageException if the cert url is invalid.
    */

    private function _validateUrl($url)
    {
        $parsed = parse_url($url);
        if (empty($parsed['scheme'])
            || empty($parsed['host'])
            || $parsed['scheme'] !== 'https'
            || substr($url, -4) !== '.pem'
            || !preg_match($this->defaultHostPattern, $parsed['host'])
        ) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                'The certificate is located on an invalid domain.'
            );
        }
    }
}
?>
