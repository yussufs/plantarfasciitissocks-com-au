#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────
# create-legal-pages.sh — Create legal pages (Privacy, Refund, Terms).
#
# Usage: ./scripts/create-legal-pages.sh <wp-path> <domain>
#
# Called by setup-local.sh and init-production.sh.
# ──────────────────────────────────────────────

WP_PATH="${1:?Usage: ./scripts/create-legal-pages.sh <wp-path> <domain>}"
DOMAIN="${2:?Usage: ./scripts/create-legal-pages.sh <wp-path> <domain>}"

echo "==> Creating legal pages..."

TMPDIR=$(mktemp -d)
trap 'rm -rf "$TMPDIR"' EXIT

find_page_by_slug() {
    wp post list --post_type=page --post_status=any --fields=ID,post_name --format=csv --path="$WP_PATH" 2>/dev/null \
        | { grep ",${1}$" || true; } | cut -d',' -f1 | head -1
}

create_page() {
    local slug="$1"
    local title="$2"
    local content_file="$3"

    local page_id
    page_id=$(find_page_by_slug "$slug")

    if [ -z "$page_id" ]; then
        page_id=$(wp post create "$content_file" \
            --post_type=page \
            --post_title="$title" \
            --post_status=publish \
            --post_name="$slug" \
            --path="$WP_PATH" \
            --porcelain 2>/dev/null | { grep -E '^[0-9]+$' || true; })
        echo "    $title created (ID: $page_id)."
    else
        wp post update "$page_id" "$content_file" \
            --post_title="$title" \
            --post_status=publish \
            --path="$WP_PATH" 2>/dev/null || true
        echo "    $title updated (ID: $page_id)."
    fi
}

# ── Privacy Policy ──────────────────────────────

cat > "$TMPDIR/privacy-policy.html" <<PRIVACY_EOF
<h3>Who we are</h3>
<p>Our website address is: <a href="https://$DOMAIN">$DOMAIN</a>.</p>

<h3>Comments</h3>
<p>When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor's IP address and browser user agent string to help spam detection.</p>
<p>An anonymized string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service privacy policy is available here: <a href="https://automattic.com/privacy/">https://automattic.com/privacy/</a>. After approval of your comment, your profile picture is visible to the public in the context of your comment.</p>

<h3>Media</h3>
<p>If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.</p>

<h3>Cookies</h3>
<p>If you leave a comment on our site you may opt-in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.</p>
<p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p>
<p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select "Remember Me", your login will persist for two weeks. If you log out of your account, the login cookies will be removed.</p>
<p>If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.</p>

<h3>Embedded content from other websites</h3>
<p>Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.</p>
<p>These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.</p>

<h3>Analytics and Tracking</h3>
<p>We use <strong>Google Analytics</strong> and other analytics services to help us understand how visitors use our site. These tools collect information such as your IP address, browser type, device information, referring/exit pages, and general location data.</p>
<ul>
<li>Google Analytics may set cookies in your browser to help analyze your usage patterns.</li>
<li>Data collected through these tools is used to improve our website, marketing, and customer experience.</li>
<li>Google may use the data collected to personalize ads within its advertising network.</li>
</ul>
<p>You can opt out of Google Analytics tracking by installing the Google Analytics Opt-out Browser Add-on.</p>
<p>We may also use tracking pixels, cookies, and similar technologies in our emails and on our site to measure campaign effectiveness and improve our services.</p>

<h3>Marketing and Retargeting</h3>
<p>We use third-party advertising services such as <strong>Google Ads, Facebook Ads, and other advertising networks</strong> to show relevant ads to you based on your interactions with our website.</p>
<ul>
<li>These services use cookies, tracking pixels, and similar technologies to serve targeted advertisements and measure ad performance.</li>
<li>This may include showing ads to you on other websites after you have visited our site (remarketing/retargeting).</li>
<li>We do not share personally identifiable information with these networks; however, they may collect or receive information from your use of our site and use it to provide measurement services and targeted ads.</li>
</ul>
<p>You can opt out of personalized ads through the following links:</p>
<ul>
<li>Google: <a href="https://adssettings.google.com">Google Ads Settings</a></li>
<li>Facebook: <a href="https://www.facebook.com/ads/preferences">Facebook Ad Preferences</a></li>
</ul>
<p>You may also manage your advertising preferences across many networks by visiting the Digital Advertising Alliance opt-out page.</p>

<h3>Who we share your data with</h3>
<p>If you request a password reset, your IP address will be included in the reset email.</p>
<p>Analytics and marketing providers such as Google Analytics and advertising networks may receive anonymized or aggregated usage data as described above.</p>

<h3>How long we retain your data</h3>
<p>If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognize and approve any follow-up comments automatically instead of holding them in a moderation queue.</p>
<p>For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.</p>

<h3>What rights you have over your data</h3>
<p>If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.</p>

<h3>Where your data is sent</h3>
<p>Visitor comments may be checked through an automated spam detection service.</p>
<p>Analytics and advertising data may be processed by third-party providers (such as Google) and stored on servers located outside your country.</p>
PRIVACY_EOF

create_page "privacy-policy" "Privacy Policy" "$TMPDIR/privacy-policy.html"

# ── Refund Policy ───────────────────────────────

cat > "$TMPDIR/refund-policy.html" <<REFUND_EOF
<p>We have a 30-day no questions asked return policy, which means you have 30 days after receiving your item to request a return or refund.</p>

<p>To start a return, you can contact us at <a href="mailto:support@$DOMAIN">support@$DOMAIN</a>. If your return is accepted, we'll immediately process a refund and give you further instructions. Items returned to us without first requesting a return will not be accepted.</p>

<p>You can always contact us for any return questions at <a href="mailto:support@$DOMAIN">support@$DOMAIN</a>.</p>

<h2>Damages and issues</h2>

<p>Please inspect your order upon reception and contact us immediately if the item is defective, damaged or if you receive the wrong item, so that we can evaluate the issue and make it right.</p>

<h2>Exceptions / non-returnable items</h2>

<p>Certain types of items cannot be returned, like perishable goods (such as food, flowers, or plants), custom products (such as special orders or personalized items), and personal care goods (such as beauty products). We also do not accept returns for hazardous materials, flammable liquids, or gases. Please get in touch if you have questions or concerns about your specific item.</p>

<p>Unfortunately, we cannot accept returns on sale items or gift cards.</p>

<h2>Exchanges</h2>

<p>The fastest way to ensure you get what you want is to return the item you have, and once the return is accepted, make a separate purchase for the new item.</p>

<h2>Refunds</h2>

<p>We will notify you once we've received and inspected your return, and let you know if the refund was approved or not. If approved, you'll be automatically refunded on your original payment method. Please remember it can take some time for your bank or credit card company to process and post the refund too.</p>
REFUND_EOF

create_page "refund-policy" "Refund Policy" "$TMPDIR/refund-policy.html"

# ── Terms of Service ────────────────────────────

cat > "$TMPDIR/terms.html" <<TERMS_EOF
<p>This website is operated by <strong>Boostup International PTY LTD</strong>. Throughout the site, the terms "we", "us" and "our" refer to Boostup International PTY LTD. Boostup International PTY LTD offers this website, including all information, tools and services available from this site to you, the user, conditioned upon your acceptance of all terms, conditions, policies and notices stated here.</p>

<p>By visiting our site and/or purchasing something from us, you engage in our "Service" and agree to be bound by the following terms and conditions ("Terms of Service", "Terms"), including those additional terms and conditions and policies referenced herein and/or available by hyperlink. These Terms apply to all users of the site, including without limitation users who are browsers, vendors, customers, merchants, and/or contributors of content.</p>

<p>Please read these Terms carefully before accessing or using our website. By accessing or using any part of the site, you agree to be bound by these Terms. If you do not agree to all the terms and conditions of this agreement, then you may not access the website or use any services. If these Terms are considered an offer, acceptance is expressly limited to these Terms.</p>

<p>Any new features or tools which are added to the current store shall also be subject to the Terms. You can review the most current version of the Terms at any time on this page. We reserve the right to update, change or replace any part of these Terms by posting updates and/or changes to our website. It is your responsibility to check this page periodically for changes. Your continued use of or access to the website following the posting of any changes constitutes acceptance of those changes.</p>

<h2>SECTION 1 – ONLINE STORE TERMS</h2>

<p>By agreeing to these Terms, you represent that you are at least the age of majority in your state or province of residence, or that you are the age of majority in your state or province of residence and you have given us your consent to allow any of your minor dependents to use this site.</p>

<p>You may not use our products for any illegal or unauthorized purpose nor may you, in the use of the Service, violate any laws in your jurisdiction (including but not limited to copyright laws).</p>

<p>You must not transmit any worms or viruses or any code of a destructive nature.</p>

<p>A breach or violation of any of the Terms will result in an immediate termination of your Services.</p>

<h2>SECTION 2 – GENERAL CONDITIONS</h2>

<p>We reserve the right to refuse service to anyone for any reason at any time.</p>

<p>You understand that your content (not including credit card information), may be transferred unencrypted and involve (a) transmissions over various networks; and (b) changes to conform and adapt to technical requirements of connecting networks or devices. Credit card information is always encrypted during transfer over networks.</p>

<p>You agree not to reproduce, duplicate, copy, sell, resell or exploit any portion of the Service, use of the Service, or access to the Service or any contact on the website through which the Service is provided, without express written permission by us.</p>

<p>The headings used in this agreement are included for convenience only and will not limit or otherwise affect these Terms.</p>

<h2>SECTION 3 – ACCURACY, COMPLETENESS AND TIMELINESS OF INFORMATION</h2>

<p>We are not responsible if information made available on this site is not accurate, complete or current. The material on this site is provided for general information only and should not be relied upon or used as the sole basis for making decisions without consulting primary, more accurate, more complete or more timely sources of information. Any reliance on the material on this site is at your own risk.</p>

<p>This site may contain certain historical information. Historical information, necessarily, is not current and is provided for your reference only.</p>

<p>We reserve the right to modify the contents of this site at any time, but we have no obligation to update any information on our site. You agree that it is your responsibility to monitor changes to our site.</p>

<h2>SECTION 4 – MODIFICATIONS TO THE SERVICE AND PRICES</h2>

<p>Prices for our products are subject to change without notice.</p>

<p>We reserve the right at any time to modify or discontinue the Service (or any part or content thereof) without notice at any time.</p>

<p>We shall not be liable to you or to any third party for any modification, price change, suspension or discontinuance of the Service.</p>

<h2>SECTION 5 – PRODUCTS OR SERVICES</h2>

<p>Certain products or services may be available exclusively online through the website. These products or services may have limited quantities and are subject to return or exchange only according to our Return Policy.</p>

<p>We have made every effort to display as accurately as possible the colors and images of our products that appear at the store. We cannot guarantee that your computer monitor's display of any color will be accurate.</p>

<p>We reserve the right, but are not obligated, to limit the sales of our products or Services to any person, geographic region or jurisdiction. We may exercise this right on a case-by-case basis.</p>

<p>We reserve the right to limit the quantities of any products or services that we offer.</p>

<p>All descriptions of products or product pricing are subject to change at any time without notice, at our sole discretion.</p>

<p>We reserve the right to discontinue any product at any time. Any offer for any product or service made on this site is void where prohibited.</p>

<p>We do not warrant that the quality of any products, services, information, or other material purchased or obtained by you will meet your expectations, or that any errors in the Service will be corrected.</p>

<h2>SECTION 6 – ACCURACY OF BILLING AND ACCOUNT INFORMATION</h2>

<p>We reserve the right to refuse any order you place with us. We may, in our sole discretion, limit or cancel quantities purchased per person, per household or per order. These restrictions may include orders placed by or under the same customer account, the same credit card, and/or orders that use the same billing and/or shipping address.</p>

<p>In the event that we make a change to or cancel an order, we may attempt to notify you by contacting the e-mail and/or billing address/phone number provided at the time the order was made.</p>

<p>We reserve the right to limit or prohibit orders that, in our sole judgment, appear to be placed by dealers, resellers or distributors.</p>

<p>You agree to provide current, complete and accurate purchase and account information for all purchases made at our store. You agree to promptly update your account and other information, including your email address and credit card numbers and expiration dates, so that we can complete your transactions and contact you as needed.</p>

<p>For more detail, please review our <a href="/refund-policy/">Return Policy</a>.</p>

<h2>SECTION 7 – OPTIONAL TOOLS</h2>

<p>We may provide you with access to third-party tools over which we neither monitor nor have any control nor input.</p>

<p>You acknowledge and agree that we provide access to such tools "as is" and "as available" without any warranties, representations or conditions of any kind and without any endorsement. We shall have no liability whatsoever arising from or relating to your use of optional third-party tools.</p>

<p>Any use by you of optional tools offered through the site is entirely at your own risk and discretion and you should ensure that you are familiar with and approve of the terms on which tools are provided by the relevant third-party provider(s).</p>

<p>We may also, in the future, offer new services and/or features through the website (including the release of new tools and resources). Such new features and/or services shall also be subject to these Terms.</p>

<h2>SECTION 8 – THIRD-PARTY LINKS</h2>

<p>Certain content, products and services available via our Service may include materials from third parties.</p>

<p>Third-party links on this site may direct you to third-party websites that are not affiliated with us. We are not responsible for examining or evaluating the content or accuracy and we do not warrant and will not have any liability or responsibility for any third-party materials or websites, or for any other materials, products, or services of third parties.</p>

<p>We are not liable for any harm or damages related to the purchase or use of goods, services, resources, content, or any other transactions made in connection with any third-party websites. Please review carefully the third party's policies and practices and make sure you understand them before you engage in any transaction. Complaints, claims, concerns, or questions regarding third-party products should be directed to the third party.</p>

<h2>SECTION 9 – USER COMMENTS, FEEDBACK AND OTHER SUBMISSIONS</h2>

<p>If, at our request, you send certain specific submissions (for example, contest entries) or without a request from us you send creative ideas, suggestions, proposals, plans, or other materials, whether online, by email, by postal mail, or otherwise (collectively, "comments"), you agree that we may, at any time, without restriction, edit, copy, publish, distribute, translate and otherwise use in any medium any comments that you forward to us.</p>

<p>We are and shall be under no obligation (1) to maintain any comments in confidence; (2) to pay compensation for any comments; or (3) to respond to any comments.</p>

<p>We may, but have no obligation to, monitor, edit or remove content that we determine in our sole discretion is unlawful, offensive, threatening, libelous, defamatory, pornographic, obscene or otherwise objectionable or violates any party's intellectual property or these Terms.</p>

<p>You agree that your comments will not violate any right of any third party, including copyright, trademark, privacy, personality or other personal or proprietary right. You further agree that your comments will not contain libelous or otherwise unlawful, abusive or obscene material, or contain any computer virus or other malware that could in any way affect the operation of the Service or any related website.</p>

<p>You may not use a false e-mail address, pretend to be someone other than yourself, or otherwise mislead us or third parties as to the origin of any comments. You are solely responsible for any comments you make and their accuracy.</p>

<p>We take no responsibility and assume no liability for any comments posted by you or any third party.</p>

<h2>SECTION 10 – PERSONAL INFORMATION</h2>

<p>Your submission of personal information through the store is governed by our <a href="/privacy-policy/">Privacy Policy</a>.</p>

<h2>SECTION 11 – ERRORS, INACCURACIES AND OMISSIONS</h2>

<p>Occasionally there may be information on our site or in the Service that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing, promotions, offers, product shipping charges, transit times and availability.</p>

<p>We reserve the right to correct any errors, inaccuracies or omissions, and to change or update information or cancel orders if any information in the Service or on any related website is inaccurate at any time without prior notice (including after you have submitted your order).</p>

<p>We undertake no obligation to update, amend or clarify information in the Service or on any related website, including without limitation, pricing information, except as required by law. No specified update or refresh date applied in the Service or on any related website should be taken to indicate that all information in the Service or on any related website has been modified or updated.</p>

<h2>SECTION 12 – PROHIBITED USES</h2>

<p>In addition to other prohibitions as set forth in the Terms, you are prohibited from using the site or its content: (a) for any unlawful purpose; (b) to solicit others to perform or participate in any unlawful acts; (c) to violate any international, federal, provincial or state regulations, rules, laws, or local ordinances; (d) to infringe upon or violate our intellectual property rights or the intellectual property rights of others; (e) to harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate based on gender, sexual orientation, religion, ethnicity, race, age, national origin, or disability; (f) to submit false or misleading information; (g) to upload or transmit viruses or any other type of malicious code that will or may be used in any way that will affect the functionality or operation of the Service or of any related website, other websites, or the Internet; (h) to collect or track the personal information of others; (i) to spam, phish, pharm, pretext, spider, crawl, or scrape; (j) for any obscene or immoral purpose; or (k) to interfere with or circumvent the security features of the Service or any related website, other websites, or the Internet.</p>

<p>We reserve the right to terminate your use of the Service or any related website for violating any of the prohibited uses.</p>

<h2>SECTION 13 – DISCLAIMER OF WARRANTIES; LIMITATION OF LIABILITY</h2>

<p>We do not guarantee, represent or warrant that your use of our Service will be uninterrupted, timely, secure or error-free.</p>

<p>We do not warrant that the results that may be obtained from the use of the Service will be accurate or reliable.</p>

<p>You agree that from time to time we may remove the Service for indefinite periods of time or cancel the Service at any time, without notice to you.</p>

<p>You expressly agree that your use of, or inability to use, the Service is at your sole risk. The Service and all products and services delivered to you through the Service are (except as expressly stated by us) provided "as is" and "as available" for your use, without any representation, warranties or conditions of any kind, either express or implied, including all implied warranties or conditions of merchantability, merchantable quality, fitness for a particular purpose, durability, title, and non-infringement.</p>

<p>In no case shall <strong>Boostup International PTY LTD</strong>, our directors, officers, employees, affiliates, agents, contractors, interns, suppliers, service providers or licensors be liable for any injury, loss, claim, or any direct, indirect, incidental, punitive, special, or consequential damages of any kind, including, without limitation lost profits, lost revenue, lost savings, loss of data, replacement costs, or any similar damages, whether based in contract, tort (including negligence), strict liability or otherwise, arising from your use of any of the Service or any products procured using the Service, or for any other claim related in any way to your use of the Service or any product, including, but not limited to, any errors or omissions in any content, or any loss or damage of any kind incurred as a result of the use of the Service or any content (or product) posted, transmitted, or otherwise made available via the Service, even if advised of their possibility. Because some states or jurisdictions do not allow the exclusion or the limitation of liability for consequential or incidental damages, in such states or jurisdictions, our liability shall be limited to the maximum extent permitted by law.</p>

<h2>SECTION 14 – INDEMNIFICATION</h2>

<p>You agree to indemnify, defend and hold harmless <strong>Boostup International PTY LTD</strong> and our parent, subsidiaries, affiliates, partners, officers, directors, agents, contractors, licensors, service providers, subcontractors, suppliers, interns and employees, from any claim or demand, including reasonable attorneys' fees, made by any third party due to or arising out of your breach of these Terms or the documents they incorporate by reference, or your violation of any law or the rights of a third party.</p>

<h2>SECTION 15 – SEVERABILITY</h2>

<p>In the event that any provision of these Terms is determined to be unlawful, void or unenforceable, such provision shall nonetheless be enforceable to the fullest extent permitted by applicable law, and the unenforceable portion shall be deemed to be severed from these Terms; such determination shall not affect the validity and enforceability of any other remaining provisions.</p>

<h2>SECTION 16 – TERMINATION</h2>

<p>The obligations and liabilities of the parties incurred prior to the termination date shall survive the termination of this agreement for all purposes.</p>

<p>These Terms are effective unless and until terminated by either you or us. You may terminate these Terms at any time by notifying us that you no longer wish to use our Services, or when you cease using our site.</p>

<p>If in our sole judgment you fail, or we suspect that you have failed, to comply with any term or provision of these Terms, we may terminate this agreement at any time without notice and you will remain liable for all amounts due up to and including the date of termination; and/or we may accordingly deny you access to our Services (or any part thereof).</p>

<h2>SECTION 17 – ENTIRE AGREEMENT</h2>

<p>The failure of us to exercise or enforce any right or provision of these Terms shall not constitute a waiver of such right or provision.</p>

<p>These Terms and any policies or operating rules posted by us on this site or in respect to the Service constitute the entire agreement and understanding between you and us and govern your use of the Service, superseding any prior or contemporaneous agreements, communications and proposals, whether oral or written, between you and us (including, but not limited to, any prior versions of the Terms).</p>

<p>Any ambiguities in the interpretation of these Terms shall not be construed against the drafting party.</p>

<h2>SECTION 18 – GOVERNING LAW</h2>

<p>These Terms and any separate agreements whereby we provide you Services shall be governed by and construed in accordance with the laws of <strong>Australia</strong>.</p>

<h2>SECTION 19 – CHANGES TO TERMS OF SERVICE</h2>

<p>You can review the most current version of the Terms at any time at this page.</p>

<p>We reserve the right, at our sole discretion, to update, change or replace any part of these Terms by posting updates and changes to our website. It is your responsibility to check our website periodically for changes. Your continued use of or access to our website or the Service following the posting of any changes to these Terms constitutes acceptance of those changes.</p>

<h2>SECTION 20 – CONTACT INFORMATION</h2>

<p>Questions about the Terms of Service should be sent to us at <a href="mailto:support@$DOMAIN">support@$DOMAIN</a>.</p>
TERMS_EOF

create_page "terms" "Terms of Service" "$TMPDIR/terms.html"

# ── Link pages in WordPress / WooCommerce settings ──

PP_ID=$(find_page_by_slug "privacy-policy")
if [ -n "$PP_ID" ]; then
    wp option update wp_page_for_privacy_policy "$PP_ID" --path="$WP_PATH" 2>/dev/null || true
    echo "    WordPress privacy policy page set."
fi

TERMS_ID=$(find_page_by_slug "terms")
if [ -n "$TERMS_ID" ]; then
    wp option update woocommerce_terms_page_id "$TERMS_ID" --path="$WP_PATH" 2>/dev/null || true
    echo "    WooCommerce terms page set."
fi
