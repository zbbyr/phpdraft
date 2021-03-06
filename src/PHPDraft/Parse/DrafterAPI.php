<?php
/**
 * This file contains the DrafterAPI.php
 *
 * @package php-drafter\SOMETHING
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

//TODO: Change this

namespace PHPDraft\Parse;


class DrafterAPI extends BaseParser
{
    /**
     * ApibToJson constructor.
     *
     * @param string $apib API Blueprint text
     */
    public function __construct($apib)
    {
        parent::__construct($apib);

        $ch = $this->curl_init_drafter("# Hello API
## /message
### GET
            + Response 200 (text/plain)

        Hello World!");

        curl_exec($ch);

        if (curl_errno($ch) !== 0) {
            throw new \RuntimeException('Drafter webservice is not available!', 1);
        }
        curl_close($ch);

        $this->tmp_dir = sys_get_temp_dir() . '/drafter';
    }

    /**
     * Parses the apib for the selected method
     *
     * @return void
     */
    protected function parse()
    {
        $ch = $this->curl_init_drafter($this->apib);

        $response = curl_exec($ch);

        if (curl_errno($ch) !== 0) {
            throw new \RuntimeException('Drafter webservice failed to parse input', 1);
        }

        $this->json = json_decode($response);
    }

    /**
     * Init curl for drafter webservice
     *
     * @param string $message API blueprint to parse
     *
     * @return resource
     */
    private function curl_init_drafter($message)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.apiblueprint.org/parser");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: text/vnd.apiblueprint",
            "Accept: application/vnd.refract.parse-result+json",
        ]);

        return $ch;
    }
}