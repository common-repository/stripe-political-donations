<?php

// Create the html that defines the payment form.
//	Note: Do not add any 'name' attributes to input elements
//	that contain sensitive data. This prevents this data from
//	being posted to your site. All sensitive card holder info
//	is only sent to Stripe.com using HTTPS.
class StripePaymentsFrontend {
    
    var $state_list = array(
        'Alabama'=>'AL',
        'Alaska'=>'AK',
        'Arizona'=>'AZ',
        'Arkansas'=>'AR',
        'California'=>'CA',
        'Colorado'=>'CO',
        'Connecticut'=>'CT',
        'Delaware'=>'DE',
        'District Of Columbia'=>'DC',
        'Florida'=>'FL',
        'Georgia'=>'GA',
        'Hawaii'=>'HI',
        'Idaho'=>'ID',
        'Illinois'=>'IL',
        'Indiana'=>'IN',
        'Iowa'=>'IA',
        'Kansas'=>'KS',
        'Kentucky'=>'KY',
        'Louisiana'=>'LA',
        'Maine'=>'ME',
        'Maryland'=>'MD',
        'Massachusetts'=>'MA',
        'Michigan'=>'MI',
        'Minnesota'=>'MN',
        'Mississippi'=>'MS',
        'Missouri'=>'MO',
        'Montana'=>'MT',
        'Nebraska'=>'NE',
        'Nevada'=>'NV',
        'New Hampshire'=>'NH',
        'New Jersey'=>'NJ',
        'New Mexico'=>'NM',
        'New York'=>'NY',
        'North Carolina'=>'NC',
        'North Dakota'=>'ND',
        'Ohio'=>'OH',
        'Oklahoma'=>'OK',
        'Oregon'=>'OR',
        'Pennsylvania'=>'PA',
        'Rhode Island'=>'RI',
        'South Carolina'=>'SC',
        'South Dakota'=>'SD',
        'Tennessee'=>'TN',
        'Texas'=>'TX',
        'Utah'=>'UT',
        'Vermont'=>'VT',
        'Virginia'=>'VA',
        'Washington'=>'WA',
        'West Virginia'=>'WV',
        'Wisconsin'=>'WI',
        'Wyoming'=>'WY'
    );
    
    // The secret
    var $api_secret = '';
    
    // The key
    var $api_key = '';
    
    // Is set to the url specified in the WP General Settings
    // This is the Domain that the SSL cert for your server is keyed to.
    // Example: donate.yourdomain.com, yourdomain.com, or www.yourdomain.com
    // OPTIONAL
    var $url_specified = '';
    
    // The default redirect URL for the thank-you page.
    var $redirect_url = null;
    
    // Populated with the fieldsets
    var $fieldsets = array();
    
    // Support phone for error messages.
    var $support_phone = '';
    
    /*
     * Construct
     * Here we populate many of the above vars from the WP options.
     */
    function __construct() {
        global $currencySymbol, $employment, $fullAddress;
        $this->api_key = get_option('ngp_api_key', '');
        $this->url_specified = get_option('ngp_secure_url', '');
        $this->support_phone = get_option('ngp_support_phone', '');
        
        $this->redirect_url = get_option('ngp_thanks_url', '/thank-you-for-your-contribution');
        
        $this->fieldsets = array(
            'Personal Information' => array(
                array(
                    'type' => 'text',
                    'slug' => 'FullName',
                    'required' => 'true',
                    'label' => 'Name',
                ),
                array(
                    'type' => 'text',
                    'slug' => 'Email',
                    'required' => 'true',
                    'label' => 'Email Address'
                )
            ),
            'Employment' => array(
                'html_intro' => $employment,
                array(
                    'type' => 'text',
                    'slug' => 'Employer',
                    'required' => 'true',
                    'label' => 'Employer'
                ),
                array(
                    'type' => 'text',
                    'slug' => 'Occupation',
                    'required' => 'true',
                    'label' => 'Occupation'
                )
            ),
            'Credit card' => array(
                'html_intro' => '<p id="accepted-cards" style="margin: 0pt 0pt -5px; background: url(/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/credit-card-logos.png) no-repeat scroll 0% 0% transparent; text-indent: -900em; width: 211px; height: 34px;">We accept Visa, Mastercard, American Express and Discover cards.</p>',
                array(
                    'type' => 'radio',
                    'id' => 'cardAmount',
                    'required' => 'true',
                    'label' => 'Amount',
                    'options' => array(
                        '1000' => '$10',
                        '2500' => '$25',
                        '5000' => '$50',
                        '10000' => '$100',
                        '50000' => '$500',
                        '100000' => '$1,000',
                        '260000' => '$2,600',
                        'custom' => '<label for="custom_dollar_amt">Other:</label> $<input type="text" %s class="custom_dollar_amt" /> <small>('.strtoupper($currencySymbol).')</small>'
                    )
                ),
                array(
                    'type' => 'text',
                    'required' => 'true',
                    'id' => 'cardNumber',
                    'label' => 'Credit Card Number'
                ),
                array(
                    'type' => 'select',
                    'required' => 'true',
                    'label' => 'Expiration Date',
                    'id' => 'cardExpiryMonth',
                    'show_label' => 'true',
                    'show_pre_div' => 'true',
                    'show_post_div' => 'false',
                    'options' => array(
                        '01' => '1 - January',
                        '02' => '2 - February',
                        '03' => '3 - March',
                        '04' => '4 - April',
                        '05' => '5 - May',
                        '06' => '6 - June',
                        '07' => '7 - July',
                        '08' => '8 - August',
                        '09' => '9 - September',
                        '10' => '10 - October',
                        '11' => '11 - November',
                        '12' => '12 - December'
                    )
                ),
                array(
                    'type' => 'select',
                    'required' => 'true',
                    'label' => 'Expiration Year',
                    'id' => 'cardExpiryYear',
                    'show_label' => 'false',
                    'show_placeholder' => 'false',
                    'show_pre_div' => 'false',
                    'options' => array()
                ),
                array(
                    'type' => 'text',
                    'required' => 'true',
                    'id' => 'cardCvc',
                    'label' => 'CVC code'
                ),
            )
            // array(
            //     'type' => 'checkbox',
            //     'slug' => 'RecurringContrib',
            //     'required' => 'true',
            //     'label' => 'Recurring Contribution?'
            //     'show_label' => 'false'
            //     'show_placeholder' => 'false'
            // )
        );
        
        if($fullAddress) {
            $this->fieldsets['Personal Information'][] = array(
                'type' => 'text',
                'slug' => 'Address1',
                'required' => 'true',
                'label' => 'Street Address'
            );
            $this->fieldsets['Personal Information'][] = array(
                'type' => 'text',
                'slug' => 'Address2',
                'required' => 'false',
                'label' => 'Street Address (Cont.)',
                'show_label' => 'false'
            );
            $this->fieldsets['Personal Information'][] = array(
                'type' => 'hidden',
                'slug' => 'City',
                'required' => 'true',
                'label' => 'City'
            );
            $this->fieldsets['Personal Information'][] = array(
                'type' => 'select',
                'slug' => 'State',
                'required' => 'true',
                'label' => 'State',
                'options' => array('AK'=>'AK','AL'=>'AL','AR'=>'AR','AZ'=>'AZ','CA'=>'CA','CO'=>'CO','CT'=>'CT','DC'=>'DC','DE'=>'DE','FL'=>'FL','GA'=>'GA','HI'=>'HI','IA'=>'IA','ID'=>'ID','IL'=>'IL','IN'=>'IN','KS'=>'KS','KY'=>'KY','LA'=>'LA','MA'=>'MA','MD'=>'MD','ME'=>'ME','MI'=>'MI','MN'=>'MN','MO'=>'MO','MS'=>'MS','MT'=>'MT','NC'=>'NC','ND'=>'ND','NE'=>'NE','NH'=>'NH','NJ'=>'NJ','NM'=>'NM','NV'=>'NV','NY'=>'NY','OH'=>'OH','OK'=>'OK','OR'=>'OR','PA'=>'PA','RI'=>'RI','SC'=>'SC','SD'=>'SD','TN'=>'TN','TX'=>'TX','UT'=>'UT','VA'=>'VA','VT'=>'VT','WA'=>'WA','WI'=>'WI','WV'=>'WV','WY'=>'WY')
            );

        }
        $this->fieldsets['Personal Information'][] = array(
            'type' => 'text',
            'slug' => 'Zip',
            'required' => 'true',
            'label' => 'Zip Code'
        );

        /*
         * Set the Year options for CC expiration to include this year
         * and 19 more years.
         */
        $y = (int)date('Y');
        $y_short = (int)date('y');
        while($y < (int)date('Y', strtotime('+19 years'))) {
            $this->fieldsets['Credit card'][3]['options'][$y_short] = $y;
            $y+=1;
            $y_short+=1;
        }
    }
    
    /*
     * Check Security
     *
     * This function not only checks that the methods are running under SSL,
     * but it also makes sure that the API Key has been configured.
     * If not under SSL and not on a .dev TLD, it redirects first to the URL
     * specified in the WP General Options panel, if not that, then to the
     * same URL as the page attempted to load.
     */
    function check_security() {
        global $wpdb, $publicKey, $secretKey, $currencySymbol;
        $state = true;
        
        $server_url_parts = explode('.', $_SERVER["SERVER_NAME"]);
        if(!empty($this->url_specified) && $server_url_parts[count($server_url_parts)-1]!=='dev') {
            $url_parts = $this->url_specified;
        } else {
            $url_parts = $server_url_parts;
        }
        if((!isset($_SERVER["HTTPS"]) || (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "on" && $_SERVER["HTTPS"]!= 1)) && $url_parts[count($url_parts)-1]!=='dev' && !isset($_GET['devtest'])) {
            if(!empty($this->url_specified)) {
                $newurl = "https://" . $this->url_specified . $_SERVER["REQUEST_URI"];
            } else {
                $newurl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }
            header("Location: $newurl");
            exit();
        }
        
        $error = "";
        if( strlen($publicKey)==0) {
            $error .= "<li>Public key is not set.</li>";
        }
        if( strlen($secretKey)==0) {
            $error .= "<li>Secret key is not set.</li>";
        }
        if( strlen($currencySymbol)==0) {
            $error .= "<li>Currency symbol is not set.</li>";
        }

        if(strlen($error)>0) {
            $error = "<div class='stripe-payment-config-errors'><p>Fix the following configuration errors before using the form.</p><ul>".$error."</ul></div>";
            $this->config_errors = $error;
        }
        
        return $state;
    }
    
    function trim_value(&$value, $chars=null) {
        if($chars)
            $value = trim($value, $chars);
        else
            $value = trim($value);
    }
    
    /**
     * Shows form used to donate
     */
    function show_form( $atts=null, $form=true ) {
        global $wpdb, $isPolitical, $currencySymbol, $eligibility;
        
        $check_security = $this->check_security();
        
        if($check_security!==true) {
            return false;
            exit();
        }
        
        extract( shortcode_atts( array(
            'amounts' => null,
            'amount_as_input' => null,
            'source' => null,
            'thanks_url' => null,
            'custom_amt_off' => 'false',
            'button' => 'Submit',
            'default_state' => null
        ), $atts ) );
        if(isset($_GET['amounts']) && !empty($_GET['amounts'])) {
            $amounts = $_GET['amounts'];
        }
        
        if(isset($_GET['source'])) {
            $source = $_GET['source'];
        } else if(isset($_GET['refcode'])) {
            $source = $_GET['refcode'];
        }
        
        if($amounts) {
            $amounts = explode(',', $amounts);
            $this->custom_amt_options = array();
            
            foreach($amounts as $amount) {
                $ths_amt = round($amount, 0);
                $ths_amt = (string) $ths_amt;
                $this->custom_amt_options[$ths_amt*100] = '$'.$ths_amt;
            }
            $this->custom_amt_options['custom'] = '<label for="custom_dollar_amt">Other:</label> $<input type="text" %s class="amount custom_dollar_amt" /> <small>('.strtoupper($currencySymbol).')</small>';
        }
        
        $form_fields = '';
        // Loop through and generate the elements
        
        if(isset($source) && !empty($source)) {
            $form_fields .= '<input type="hidden" name="Source" value="'.$source.'" />';
        }
        
        if(isset($this->config_errors) && !empty($this->config_errors)) {
            $form_fields .= $this->config_errors;
        }
        foreach($this->fieldsets as $fieldset_name => $fields) {
            if($isPolitical!=='true' && $fieldset_name=='Employment') {
                continue;
            } else {
                if(isset($thanks_url)) {
                    $form_fields .= '<input type="hidden" value="'.$thanks_url.'" id="thanks_url" />';
                }
                $form_fields .= '<fieldset><legend>'.$fieldset_name.'</legend>';
                if(isset($fields['html_intro'])) {
                    $form_fields .= $fields['html_intro'];
                    unset($fields['html_intro']);
                }
                foreach($fields as $field_key => $field) {
                    if(!isset($field['type'])) {
                        var_dump($field);
                    }
                    switch($field['type']) {
                        case 'text':
                            if(!isset($field['show_pre_div']) || $field['show_pre_div']=='true') {
                                $form_fields .= '
                                    <div class="input';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error';
                                }
                                $form_fields .= '">';
                            }
                            if(isset($field['error']) && $field['error']===true) {
                                $form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
                            }
                            if(isset($field['slug'])) {
                                if(!isset($field['show_label']) || $field['show_label']!='false') {
                                    $form_fields .= '
                                            <label for="'.$field['slug'].'">'.$field['label'];
                                    if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                                    $form_fields .= '</label>';
                                }
                                $form_fields .= '<input type="text" ';
                                $form_fields .= 'name="'.$field['slug'].'" id="'.$field['slug'].'" value="';
                                if(isset($_POST[$field['slug']])) {
                                    $form_fields .= $_POST[$field['slug']];
                                }
                                $form_fields .= '"';
                            } else {
                                if(!isset($field['show_label']) || $field['show_label']!='false') {
                                    $form_fields .= '
                                            <label for="'.$field['id'].'">'.$field['label'];
                                    if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                                    $form_fields .= '</label>';
                                }
                                $form_fields .= '<input type="text" ';
                                $form_fields .= ' id="'.$field['id'].'" value=""';
                            }
                            if(!empty($field['label']) && (!isset($field['show_placeholder']) || $field['show_placeholder']=='true')) {
                                $form_fields .= ' placeholder="'.$field['label'].'"';
                            }
                            $form_fields .= ' />';
                            if(!isset($field['show_post_div']) || $field['show_post_div']=='true') {
                                $form_fields .= '</div>';
                            }
                            break;
                        case 'file':
                            $file = true;
                            $form_fields .= '
                                <div class="file';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error';
                                }
                                $form_fields .= '">';
                                if(isset($field['error']) && $field['error']===true && $field['required']=='true') {
                                    $form_fields .= '<div class="errMsg">You must provide a '.$field['label'].'.</div>';
                                } else if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= '<div class="errMsg">There was a problem uploading your file.</div>';
                                }
                        
                                $form_fields .= '
                                        <label for="'.$field['slug'].'">'.$field['label'];
                                if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                                $form_fields .= '</label>
                                    <input type="file" name="'.$field['slug'].'" id="'.$field['slug'].'" />
                                </div>
                            ';
                            break;
                        case 'hidden':
                            $form_fields .= '<input type="hidden" name="'.$field['slug'].'" id="'.$field['slug'].'" value="';
                            if(isset($_POST[$field['slug']])) {
                                $form_fields .= $_POST[$field['slug']];
                            } else if(isset($field['value'])) {
                                $form_fields .= $field['value'];
                            }
                            $form_fields .= '" />';
                            break;
                        case 'password':
                            $form_fields .= '
                            <div class="password    ';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error';
                                }
                                $form_fields .= '">';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
                                }
                                $form_fields .= '
                                        <label for="'.$field['slug'].'">'.$field['label'];
                                if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                                $form_fields .= '</label>
                            <input type="password" name="'.$field['slug'].'" id="'.$field['slug'].'" value="';
                            if(isset($_POST[$field['slug']])) {
                                $form_fields .= $_POST[$field['slug']];
                            }
                            $form_fields .= '"/>
                            </div>
                            ';
                            break;
                        case 'textarea':
                            $form_fields .= '
                            <div class="textarea';
                            if(isset($field['error']) && $field['error']===true) {
                                $form_fields .= ' error';
                            }
                            $form_fields .= '">';
                            if(isset($field['error']) && $field['error']===true) {
                                $form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
                            }
                            $form_fields .= '
                                    <label for="'.$field['slug'].'">'.$field['label'];
                            if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                            $form_fields .= '</label>
                            <textarea name="'.$field['slug'].'" id="'.$field['slug'].'">';
                            if(isset($_POST[$field['slug']])) {
                                $form_fields .= $_POST[$field['slug']];
                            }
                            $form_fields .= '</textarea>
                            </div>
                            ';
                            break;
                        case 'checkbox':
                            if(isset($field['options']) && !empty($field['options'])) {
                                $form_fields .= '<fieldset id="ngp_'.$field['slug'].'" class="checkboxgroup';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error">
                                    <div class="errMsg">You must check at least one.</div>';
                                } else {
                                    $form_fields .= '">';
                                }
                                $form_fields .= '<legend>'.$field['label'];
                                if($field['required']=='true') $form_fields .= '<span class="required">*</span>';
                                $form_fields .= '</legend>';
                                $i = 0;
                                foreach($field['options'] as $val) {
                                    $i++;
                                    $form_fields .= '<div class="checkboxoption"><input type="checkbox" value="'.$val.'" name="'.$field['slug'].'['.$i.']['.$val.']" id="option_'.$i.'_'.$field['slug'].'" class="'.$field['slug'].'" /> <label for="option_'.$i.'_'.$field['slug'].'">'.$val.'</label></div>'."\r\n";
                                }
                                $form_fields .= '</fieldset>';
                            } else {
                                $form_fields .= '<div id="ngp_'.$field['slug'].'" class="checkbox">';
                                $form_fields .= '<div class="checkboxoption"><input type="checkbox" name="'.$field['slug'].'" id="'.$field['slug'].'" class="'.$field['slug'].'" /> <label for="'.$field['slug'].'">'.$field['label'].'</label></div>'."\r\n";
                                $form_fields .= '</div>';
                            }
                            break;
                        case 'radio':
                            if(isset($field['slug'])) {
                                $form_fields .= '
                                <fieldset id="radiogroup_'.$field['slug'].'" class="radiogroup';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error';
                                }
                            } else {
                                $form_fields .= '
                                <fieldset id="radiogroup_'.$field['id'].'" class="radiogroup';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error';
                                }
                            }
                            $form_fields .= '"><legend>'.$field['label'];
                            if($field['required']=='true') { $form_fields .= '<span class="required">*</span>'; }
                            $form_fields .= '</legend>';
                            if(isset($field['error']) && $field['error']===true) {
                                $form_fields .= '<div class="errMsg">You must select an option.</div>';
                            }
                            $i = 0;
                            if($field['label']=='Amount' && isset($this->custom_amt_options)) {
                                $the_options = $this->custom_amt_options;
                            } else {
                                $the_options = $field['options'];
                            }
                            
                            if(isset($_GET['amt']) && empty($_POST)) {
                                if(strpos($_GET['amt'], '.')===false) {
                                    $get_amt = $_GET['amt'];
                                } else {
                                    $get_amt = $_GET['amt'];
                                }
                                
                                if(array_key_exists($get_amt, $the_options)) {
                                    $amt = $get_amt;
                                } else {
                                    $custom_amt = $_GET['amt'];
                                }
                            } else if(isset($_POST['custom_dollar_amt'])) {
                                $custom_amt = $_POST['custom_dollar_amt'];
                            } else if(isset($field['slug']) && isset($_POST[$field['slug']])) {
                                $amt = $_POST[$field['slug']];
                            }
                            
                            foreach($the_options as $val => $labe) {
                                $i++;
                                if($val=='custom' && $custom_amt_off=='false') {
                                    $replace = (isset($custom_amt)) ? 'value="'.$custom_amt.'"' : '';
                                    $form_fields .= '<div class="radio custom-donation-amt">'.sprintf($labe, $replace).'</div>'."\r\n";
                                } else {
                                    $form_fields .= '<div class="radio"><input type="radio" value="'.$val.'"';
                                    if(isset($field['slug'])) {
                                        $form_fields .= ' name="'.$field['slug'].'"';
                                        $form_fields .= ' id="'.$i.'_'.$field['slug'].'" class="amount '.$field['slug'].'"';
                                    } else {
                                        $form_fields .= ' id="'.$i.'_'.$field['id'].'" class="amount '.$field['id'].'"';
                                    }
                                    if(isset($amt) && $amt==$val) {
                                        $form_fields .= ' checked';
                                    }
                                    $form_fields .= '> <label for="'.$i.'_';
                                    if(isset($field['slug'])) {
                                        $form_fields .= $field['slug'];
                                    } else {
                                        $form_fields .= $field['id'];
                                    }
                                    $form_fields .= '">'.$labe.'</label></div>'."\r\n";
                                }
                            }
                            $form_fields .= '</fieldset>';
                            break;
                        case 'select':
                            if(!isset($field['show_pre_div']) || $field['show_pre_div']=='true') {
                                $form_fields .= '
                                    <div class="input';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error';
                                }
                                $form_fields .= '">';
                            }
                            if(isset($field['error']) && $field['error']===true) {
                                $form_fields .= '<div class="errMsg">You must select an option.</div>';
                            }
                            if(isset($field['slug'])) {
                                if(!isset($field['show_label']) || $field['show_label']!='false') {
                                    $form_fields .= '
                                            <label for="'.$field['slug'].'">'.$field['label'];
                                    if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                                    $form_fields .= '</label>';
                                }
                                $form_fields .= '<select name="'.$field['slug'].'" id="'.$field['slug'].'">'."\r\n";
                            } else {
                                if(!isset($field['show_label']) || $field['show_label']!='false') {
                                    $form_fields .= '
                                            <label for="'.$field['id'].'">'.$field['label'];
                                    if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                                    $form_fields .= '</label>';
                                }
                                $form_fields .= '<select id="'.$field['id'].'">'."\r\n";
                            }
                            $field_ref = (isset($field['slug'])) ? $field['slug'] : $field['id'];
                            if($field_ref!='State' && $field_ref!='cardExpiryMonth' && $field_ref!='cardExpiryYear') {
                                $form_fields .= '
                                <option>Select an option...</option>
                                ';
                            }
                            foreach($field['options'] as $key => $val) {
                                $form_fields .= '<option value="'.$key.'"';
                                if(isset($field['slug']) && isset($_POST[$field['slug']]) && $_POST[$field['slug']]==$key) {
                                    $form_fields .= ' selected="selected"';
                                } else if(!empty($default_state) && $default_state==$key) {
                                    $form_fields .= ' selected="selected"';
                                }
                                $form_fields .= '>'.$val.'</option>'."\r\n";
                            }
                            $form_fields .= '</select>';
                            if(!isset($field['show_post_div']) || $field['show_post_div']=='true') {
                                $form_fields .= '</div>';
                            }
                            break;
                        case 'multiselect':
                            $form_fields .= '
                            <div class="multiselect    ';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= ' error';
                                }
                                $form_fields .= '">';
                                if(isset($field['error']) && $field['error']===true) {
                                    $form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
                                }
                                $form_fields .= '
                                        <label for="'.$field['slug'].'">'.$field['label'];
                                if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
                                $form_fields .= '</label>
                                <select multiple name="'.$field['slug'].'" id="'.$field['slug'].'">'."\r\n";
                                    foreach($field['options'] as $key => $val) {
                                        $form_fields .= '<option value="'.$key.'">'.$val.'</option>'."\r\n";
                                    }
                                    $form_fields .= '
                                </select>
                            </div>
                            ';
                            break;
                    }
                }
                $form_fields .= '</fieldset>';
            }
        }
        
        $return = '';
        
        if(!empty($form_fields)) {
            $return .= '<div id="stripe-msgs" style="display:none;"></div><form name="stripe_payment" class="stripe_payment_submission" id="stripe-payment-form" action="'.$_SERVER['REQUEST_URI'].'" method="post">';
            
            // if(function_exists('wp_nonce_field')) {
            //     $return .= wp_nonce_field('stripe_nonce_field', 'stripe_add', true, false);
            // }
            
            $return .= $form_fields;
            
            $return .= '<div class="submit">
                <input type="submit" value="'.$button.'" />
            </div>
            <div class="stripe-payment-form-row-progress">
                <span class="message"></span>
            </div>';
            if($isPolitical==='true') {
                $return .= str_replace('{{button}}', $button, $eligibility);
            }
            // $return .= '<p class="addtl-donation-footer-info">'.str_replace("\r\n", '<br />', str_replace('&lt;i&gt;', '<i>', str_replace('&lt;/i&gt;', '</i>', str_replace('&lt;u&gt;', '<u>', str_replace('&lt;/u&gt;', '</u>', str_replace('&lt;b&gt;', '<b>', str_replace('&lt;/b&gt;', '</b>', get_option('stripe_payments_footer_info')))))))).'</p>';
            $return .= '</form>';
            
            return $return;
        }
    }
}

$stripePaymentsFrontend = new StripePaymentsFrontend();

// function signalfade_stripe_payments_process_form() {
//     global $stripePaymentsFrontend;
//     $stripePaymentsFrontend->process_form();
// }

function signalfade_stripe_payments_show_form($atts=null) {
    global $stripePaymentsFrontend;
    return $stripePaymentsFrontend->show_form($atts);
}