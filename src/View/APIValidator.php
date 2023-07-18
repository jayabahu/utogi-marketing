<?php

namespace UtogiMarketing\View;

class APIValidator
{
    public function __invoke($token)
    {
        $query = <<<'JSON'
            {
                marketing {
                    campaigns(type: PROPERTY, pagination: { page: 1, perPage: 1 }) {
                        data {
                            id
                            name
                            status
                        }
                    }
                }
            }
        JSON;

        $result = query($query, [], $token);

        if (!$result['data']['marketing']) {
            add_settings_error(
                'utogi-api-key',
                // whatever you registered in `register_setting
                'a_code_here',
                // doesn't really mater
                __('Invalid api key', 'wpse'),
                'error' // error or notice works to make things pretty
            );
        }

        return $token;
    }

}