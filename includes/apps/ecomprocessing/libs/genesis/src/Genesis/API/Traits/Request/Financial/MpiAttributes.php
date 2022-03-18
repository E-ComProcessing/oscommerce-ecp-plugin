<?php
/*
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @license     http://opensource.org/licenses/MIT The MIT License
 */

namespace Genesis\API\Traits\Request\Financial;

use Genesis\Exceptions\ErrorParameter;
use Genesis\API\Constants\Transaction\Parameters\MpiProtocolVersions;

/**
 * Trait MpiAttributes
 * @package Genesis\API\Traits\Request\Financial
 *
 * @method $this setMpiCavv($value) Set the Verification Id of the authentication.
 * @method $this setMpiEci($value) Set Electric Commerce Indicator as returned from the MPI.
 * @method $this setMpiXid($value) Set Transaction ID that uniquely identifies a 3D Secure check request
 * @method $this setMpiDirectoryServerId($value) Set the directory server ID used during 3DS authentication
 */
trait MpiAttributes
{
    /**
     * Verification Id of the authentication.
     *
     * Please note this can be the CAVV for Visa Card or UCAF to identify MasterCard.
     *
     * @var string
     */
    protected $mpi_cavv;

    /**
     * Electric Commerce Indicator as returned from the MPI.
     *
     * @var string
     */
    protected $mpi_eci;

    /**
     * Transaction ID generated by the 3D Secure service
     * that uniquely identifies a 3D Secure check request
     *
     * @var string
     */
    protected $mpi_xid;

    /**
     * The used 3DS protocol version. Default is 1 if not supplied.
     *
     * @var string
     */
    protected $mpi_protocol_version;

    /**
     * The directory server ID used during 3DS authentication.
     *
     * @var string
     */
    protected $mpi_directory_server_id;

    /**
     * Validate Protocol Version
     *
     * @param $value
     * @return MpiAttributes
     * @throws ErrorParameter
     */
    public function setMpiProtocolVersion($value)
    {
        $protocolVersions = MpiProtocolVersions::getAll();

        if (!in_array($value, $protocolVersions)) {
            throw new ErrorParameter(
                sprintf(
                    'Protocol version (%s) is not valid. Valid Protocol versions are: %s ',
                    $value,
                    implode(', ', $protocolVersions)
                )
            );
        }

        $this->mpi_protocol_version = (string)$value;

        return $this;
    }

    /**
     * Builds an array list with all Params
     *
     * @return array
     */
    protected function getMpiParamsStructure()
    {
        return $this->is3DSv2() ?
            $this->get3DSv2ParamsStructure() : $this->get3DSv1ParamsStructure();
    }

    /**
     * Check which protocol version is used.
     *
     * @return bool
     */
    protected function is3DSv2()
    {
        return $this->mpi_protocol_version === MpiProtocolVersions::PROTOCOL_VERSION_2;
    }

    /**
     * Get 3DSv1 parameters structure
     *
     * @return array
     */
    protected function get3DSv1ParamsStructure()
    {
        return [
            'cavv' => $this->mpi_cavv,
            'eci'  => $this->mpi_eci,
            'xid'  => $this->mpi_xid
        ];
    }

    /**
     * Get 3DSv2 parameters structure
     *
     * @return array
     */
    protected function get3DSv2ParamsStructure()
    {
        return [
            'cavv'                => $this->mpi_cavv,
            'eci'                 => $this->mpi_eci,
            'protocol_version'    => $this->mpi_protocol_version,
            'directory_server_id' => $this->mpi_directory_server_id
        ];
    }

    protected function requiredMpiFieldsConditional()
    {
        return [
            'mpi_protocol_version' => [
                MpiProtocolVersions::PROTOCOL_VERSION_2 => ['mpi_directory_server_id']
            ]
        ];
    }
}
