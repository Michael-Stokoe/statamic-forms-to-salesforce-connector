<?php

declare(strict_types=1);

namespace Stokoe\FormsToSalesforceConnector;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stokoe\FormsToWherever\Contracts\ConnectorInterface;
use Statamic\Forms\Submission;

class SalesforceConnector implements ConnectorInterface
{
    public function handle(): string
    {
        return 'salesforce';
    }

    public function name(): string
    {
        return 'Salesforce';
    }

    public function fieldset(): array
    {
        return [
            [
                'handle' => 'instance_url',
                'field' => [
                    'type' => 'text',
                    'display' => 'Instance URL',
                    'instructions' => 'Your Salesforce instance URL (e.g., https://yourinstance.salesforce.com)',
                    'validate' => 'required',
                ],
            ],
            [
                'handle' => 'access_token',
                'field' => [
                    'type' => 'text',
                    'display' => 'Access Token',
                    'instructions' => 'OAuth access token for Salesforce API',
                    'validate' => 'required',
                ],
            ],
            [
                'handle' => 'object_type',
                'field' => [
                    'type' => 'select',
                    'display' => 'Object Type',
                    'instructions' => 'Salesforce object to create',
                    'default' => 'Lead',
                    'options' => [
                        'Lead' => 'Lead',
                        'Contact' => 'Contact',
                        'Account' => 'Account',
                        'Opportunity' => 'Opportunity',
                        'Case' => 'Case',
                    ],
                ],
            ],
            [
                'handle' => 'email_field',
                'field' => [
                    'type' => 'text',
                    'display' => 'Email Field',
                    'instructions' => 'Form field containing the email address',
                    'default' => 'email',
                ],
            ],
            [
                'handle' => 'first_name_field',
                'field' => [
                    'type' => 'text',
                    'display' => 'First Name Field',
                    'instructions' => 'Form field containing the first name',
                    'default' => 'first_name',
                ],
            ],
            [
                'handle' => 'last_name_field',
                'field' => [
                    'type' => 'text',
                    'display' => 'Last Name Field',
                    'instructions' => 'Form field containing the last name',
                    'default' => 'last_name',
                ],
            ],
            [
                'handle' => 'company_field',
                'field' => [
                    'type' => 'text',
                    'display' => 'Company Field',
                    'instructions' => 'Form field containing the company name',
                    'default' => 'company',
                ],
            ],
            [
                'handle' => 'lead_source',
                'field' => [
                    'type' => 'text',
                    'display' => 'Lead Source',
                    'instructions' => 'Static value for Lead Source field',
                    'default' => 'Website',
                ],
            ],
            [
                'handle' => 'field_mapping',
                'field' => [
                    'type' => 'grid',
                    'display' => 'Field Mapping',
                    'instructions' => 'Map form fields to Salesforce object fields',
                    'fields' => [
                        [
                            'handle' => 'form_field',
                            'field' => [
                                'type' => 'text',
                                'display' => 'Form Field',
                                'width' => 50,
                            ],
                        ],
                        [
                            'handle' => 'salesforce_field',
                            'field' => [
                                'type' => 'text',
                                'display' => 'Salesforce Field API Name',
                                'instructions' => 'e.g., Phone, Website, Description',
                                'width' => 50,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function process(Submission $submission, array $config): void
    {
        $instanceUrl = rtrim($config['instance_url'] ?? '', '/');
        $accessToken = $config['access_token'] ?? null;
        $objectType = $config['object_type'] ?? 'Lead';
        $emailField = $config['email_field'] ?? 'email';
        $firstNameField = $config['first_name_field'] ?? 'first_name';
        $lastNameField = $config['last_name_field'] ?? 'last_name';
        $companyField = $config['company_field'] ?? 'company';
        $leadSource = $config['lead_source'] ?? 'Website';
        $fieldMapping = $config['field_mapping'] ?? [];

        if (!$instanceUrl || !$accessToken) {
            Log::warning('Salesforce connector: Missing instance URL or access token', [
                'form' => $submission->form()->handle(),
                'submission_id' => $submission->id(),
            ]);
            return;
        }

        $formData = $submission->data()->toArray();
        $email = $formData[$emailField] ?? null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Salesforce connector: Invalid or missing email', [
                'form' => $submission->form()->handle(),
                'submission_id' => $submission->id(),
                'email_field' => $emailField,
                'email' => $email,
            ]);
            return;
        }

        // Build Salesforce object data
        $objectData = [
            'Email' => $email,
        ];

        // Add standard fields based on object type
        if ($objectType === 'Lead') {
            if (isset($formData[$lastNameField])) {
                $objectData['LastName'] = $formData[$lastNameField];
            } else {
                $objectData['LastName'] = 'Unknown'; // Required field for Lead
            }

            if (isset($formData[$firstNameField])) {
                $objectData['FirstName'] = $formData[$firstNameField];
            }

            if (isset($formData[$companyField])) {
                $objectData['Company'] = $formData[$companyField];
            } else {
                $objectData['Company'] = 'Unknown'; // Required field for Lead
            }

            $objectData['LeadSource'] = $leadSource;
        } elseif ($objectType === 'Contact') {
            if (isset($formData[$lastNameField])) {
                $objectData['LastName'] = $formData[$lastNameField];
            } else {
                $objectData['LastName'] = 'Unknown'; // Required field for Contact
            }

            if (isset($formData[$firstNameField])) {
                $objectData['FirstName'] = $formData[$firstNameField];
            }
        }

        // Add custom field mappings
        // Skip null/empty values when key already exists to support conditional fields
        foreach ($fieldMapping as $mapping) {
            $formField = $mapping['form_field'] ?? '';
            $salesforceField = $mapping['salesforce_field'] ?? '';

            if ($formField && $salesforceField && isset($formData[$formField])) {
                $value = $formData[$formField];
                
                // Skip null/empty values to allow multiple conditional fields
                // to map to the same Salesforce field (first non-empty value wins)
                if (array_key_exists($salesforceField, $objectData) && ($value === null || $value === '')) {
                    continue;
                }
                
                $objectData[$salesforceField] = $value;
            }
        }

        $url = "{$instanceUrl}/services/data/v58.0/sobjects/{$objectType}";
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($url, $objectData);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Salesforce object created successfully', [
                    'object_type' => $objectType,
                    'object_id' => $result['id'] ?? null,
                    'email' => $email,
                    'form' => $submission->form()->handle(),
                    'submission_id' => $submission->id(),
                ]);
            } else {
                $error = $response->json();
                Log::error('Salesforce API error', [
                    'status' => $response->status(),
                    'error' => $error[0]['message'] ?? 'Unknown error',
                    'errors' => $error,
                    'full_response' => $error,
                    'email' => $email,
                    'form' => $submission->form()->handle(),
                    'submission_id' => $submission->id(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Salesforce connector exception', [
                'error' => $e->getMessage(),
                'email' => $email,
                'form' => $submission->form()->handle(),
                'submission_id' => $submission->id(),
            ]);
        }
    }
}
