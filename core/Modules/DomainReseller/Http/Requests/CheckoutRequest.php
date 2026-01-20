<?php

namespace Modules\DomainReseller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "auto_renew" => "nullable",
            "period" => "required|integer",
            "nameFirst" => "required|string",
            "nameLast" => "required|string",
            "nameMiddle" => "nullable|string",
            "email" => "required|email",
            "phone" => "required|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "fax" => "nullable|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "jobTitle" => "nullable",
            "organization" => "nullable",
            "country" => "required",
            "state" => "required",
            "city" => "required",
            "postalCode" => "required",
            "address1" => "required",
            "address2" => "nullable",

            "contact_billing_was_unchecked" => "sometimes",
            "contact_registrant_was_unchecked" => "sometimes",
            "contact_tech_was_unchecked" => "sometimes",

            "contact_billing_same_contact_admin" => "nullable",
            "contact_registrant_same_contact_admin" => "nullable",
            "contact_tech_same_contact_admin" => "nullable",

            "contact_billing_nameFirst" => "required_if:contact_billing_same_contact_admin, selected",
            "contact_billing_nameLast" => "required_if:contact_billing_same_contact_admin, selected",
            "contact_billing_nameMiddle" => "nullable",
            "contact_billing_email" => "required_if:contact_billing_same_contact_admin, selected|email",
            "contact_billing_phone" => "required_if:contact_billing_same_contact_admin, selected|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "contact_billing_fax" => "nullable|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "contact_billing_jobTitle" => "nullable",
            "contact_billing_organization" => "nullable",
            "contact_billing_country" => "required_if:contact_billing_same_contact_admin, selected",
            "contact_billing_state" => "required_if:contact_billing_same_contact_admin, selected",
            "contact_billing_city" => "required_if:contact_billing_same_contact_admin, selected",
            "contact_billing_postalCode" => "required_if:contact_billing_same_contact_admin, selected|integer",
            "contact_billing_address1" => "required_if:contact_billing_same_contact_admin, selected",
            "contact_billing_address2" => "nullable",

            "contact_registrant_nameFirst" => "required_if:contact_registrant_same_contact_admin, selected",
            "contact_registrant_nameLast" => "required_if:contact_registrant_same_contact_admin, selected",
            "contact_registrant_nameMiddle" => "nullable",
            "contact_registrant_email" => "required_if:contact_registrant_same_contact_admin, selected|email",
            "contact_registrant_phone" => "required_if:contact_registrant_same_contact_admin, selected|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "contact_registrant_fax" => "nullable|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "contact_registrant_jobTitle" => "nullable",
            "contact_registrant_organization" => "nullable",
            "contact_registrant_country" => "required_if:contact_registrant_same_contact_admin, selected",
            "contact_registrant_state" => "required_if:contact_registrant_same_contact_admin, selected",
            "contact_registrant_city" => "required_if:contact_registrant_same_contact_admin, selected",
            "contact_registrant_postalCode" => "required_if:contact_registrant_same_contact_admin, selected|integer",
            "contact_registrant_address1" => "required_if:contact_registrant_same_contact_admin, selected",
            "contact_registrant_address2" => "nullable",

            "contact_tech_nameFirst" => "required_if:contact_tech_same_contact_admin, selected",
            "contact_tech_nameLast" => "required_if:contact_tech_same_contact_admin, selected",
            "contact_tech_nameMiddle" => "nullable",
            "contact_tech_email" => "required_if:contact_tech_same_contact_admin, selected|email",
            "contact_tech_phone" => "required_if:contact_tech_same_contact_admin, selected|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "contact_tech_fax" => "nullable|regex:/^\+([0-9]){1,3}\.([0-9]\ ?){5,14}$/",
            "contact_tech_jobTitle" => "nullable",
            "contact_tech_organization" => "nullable",
            "contact_tech_country" => "required_if:contact_tech_same_contact_admin, selected",
            "contact_tech_state" => "required_if:contact_tech_same_contact_admin, selected",
            "contact_tech_city" => "required_if:contact_tech_same_contact_admin, selected",
            "contact_tech_postalCode" => "required_if:contact_tech_same_contact_admin, selected|integer",
            "contact_tech_address1" => "required_if:contact_tech_same_contact_admin, selected",
            "contact_tech_address2" => "nullable",

            "selected_payment_gateway" => 'required'
        ];
    }

    public function attributes(): array
    {
        return [
            "nameFirst" => "first name",
            "nameLast" => "last name",
            "nameMiddle" => "middle name",
            "address1" => "address one",
            "address2" => "address two",

            "contact_billing_nameFirst" => "contact billing first name",
            "contact_billing_nameLast" => "contact billing last name",
            "contact_billing_email" => "contact billing email",
            "contact_billing_phone" => "contact billing phone",
            "contact_billing_country" => "contact billing country",
            "contact_billing_state" => "contact billing state",
            "contact_billing_city" => "contact billing city",
            "contact_billing_postalCode" => "contact billing postal code",
            "contact_billing_address1" => "contact billing address one",
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['nameMiddle'] = $data['nameMiddle'] ?? '';
        $data['fax'] = $data['fax'] ?? '';
        $data['jobTitle'] = $data['jobTitle'] ?? '';
        $data['organization'] = $data['organization'] ?? '';
        $data['address2'] = $data['address2'] ?? '';
        $data['auto_renew'] = array_key_exists('auto_renew', $data) && $data["auto_renew"] === "yes";

        return $data;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
