<?php

namespace App\Traits\Zatca;

use App\Enums\Zatca;
use Illuminate\Support\Str;
use Mockery\Exception;

trait ZatcaCsr
{
    public function generateNewKeysAndCSR(string $solution_name, $mode): array
    {
        $private_key = $this->generateSecp256k1KeyPair();
        return [$private_key, $this->generateCSR($solution_name, $private_key, $mode)];
    }

    private function generateSecp256k1KeyPair(): string
    {
        $result = shell_exec('openssl ecparam -name secp256k1 -genkey');

        $result = explode('-----BEGIN EC PRIVATE KEY-----', $result);

        if (!isset($result[1])) {
            throw new Exception('Error no private key found in OpenSSL output.');
        }

        $result = trim($result[1]);

        $private_key = "-----BEGIN EC PRIVATE KEY-----\n{$result}";
        return trim($private_key);
    }

    private function generateCSR(string $solution_name, $private_key, $mode): string
    {
        if (!$private_key) {
            throw new Exception('EGS has no private key');
        }

        if (!is_dir(__DIR__ . '/tmp/')) {
            mkdir(__DIR__ . '/tmp/', 0775);
        }

        $private_key_file_name = __DIR__ . '/tmp/' . (string)Str::orderedUuid() . '.pem';
        $csr_config_file_name = __DIR__ . '/tmp/' . (string)Str::orderedUuid() . '.cnf';
        $private_key_file = fopen($private_key_file_name, 'w');
        $csr_config_file = fopen($csr_config_file_name, 'w');


        require __DIR__ . '/templates/csr_template.php';
        fwrite($private_key_file, $private_key);
        fwrite($csr_config_file, $this->defaultCSRConfig($solution_name, $mode));

        $result = shell_exec("openssl req -new -sha256 -key {$private_key_file_name} -config {$csr_config_file_name}");
        //dd($result);

        /*$cmd = "openssl req -new -sha256 -key \"$private_key_file_name\" -config \"$csr_config_file_name\" -out csr.pem 2>&1";
        $result = shell_exec($cmd);
        dd($result);*/

        $result = explode('-----BEGIN CERTIFICATE REQUEST-----', $result);
        $result = $result[1];

        $csr = "-----BEGIN CERTIFICATE REQUEST-----{$result}";

        unlink($private_key_file_name);
        unlink($csr_config_file_name);
        return $csr;
    }

    private function defaultCSRConfig(string $solution_name, $mode): array|string
    {
        $config = [
            'egs_model' => $this->egs_info['model'],
            'egs_serial_number' => $this->egs_info['uuid'],
            'solution_name' => $solution_name,
            'vat_number' => $this->egs_info['VAT_number'],
            'branch_location' => $this->egs_info['location']['building'] . ' ' . $this->egs_info['location']['street'] . ', ' . $this->egs_info['location']['city'],
            'branch_industry' => $this->egs_info['branch_industry'],
            'branch_name' => $this->egs_info['branch_name'],
            'taxpayer_name' => $this->egs_info['VAT_name'],
            'taxpayer_provided_id' => $this->egs_info['custom_id'],
            'production' => $mode == Zatca::CORE_TEXT
        ];

        $template_csr = require __DIR__ . '/templates/csr_template.php';
        $template_csr = str_replace('SET_PRIVATE_KEY_PASS', ($config['private_key_pass'] ?? 'SET_PRIVATE_KEY_PASS'), $template_csr);
        $template_csr = str_replace('SET_PRODUCTION_VALUE', ($config['production'] ? 'ZATCA-Code-Signing' : 'PREZATCA-Code-Signing'), $template_csr);
        $template_csr = str_replace('SET_EGS_SERIAL_NUMBER', "1-{$config['solution_name']}|2-{$config['egs_model']}|3-{$config['egs_serial_number']}", $template_csr);
        $template_csr = str_replace('SET_VAT_REGISTRATION_NUMBER', $config['vat_number'], $template_csr);
        $template_csr = str_replace('SET_BRANCH_LOCATION', $config['branch_location'], $template_csr);
        $template_csr = str_replace('SET_BRANCH_INDUSTRY', $config['branch_industry'], $template_csr);
        $template_csr = str_replace('SET_COMMON_NAME', $config['taxpayer_provided_id'], $template_csr);
        $template_csr = str_replace('SET_BRANCH_NAME', $config['branch_name'], $template_csr);
        return str_replace('SET_TAXPAYER_NAME', $config['taxpayer_name'], $template_csr);
    }
}
