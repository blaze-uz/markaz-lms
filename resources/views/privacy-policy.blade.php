<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
</head>
<body>
    <header>
        <h1>Privacy Policy</h1>
        <p>Effective Date: {{ dateFormat($effective_date) }}</p>
    </header>

    <section>
        <h2>Introduction</h2>
        <p>
            {{ $name}} ("we," "our," or "us") values your privacy and is committed to protecting your personal data.
            This Privacy Policy outlines how we collect, use, disclose, and protect your information when you visit our website
            <a href="{{$site_url}}">{{$site_url}}</a>. By accessing or using our website, you agree to the practices described in this Privacy Policy.
        </p>
    </section>

    <section>
        <h2>1. Information We Collect</h2>
        <p>We may collect the following types of information:</p>
        <ul>
            <li>
                <strong>Personal Information:</strong> Name, email address, phone number, billing information, etc., provided when you fill out forms, sign up for newsletters, or make purchases.
            </li>
            <li>
                <strong>Non-Personal Information:</strong> Browser type, device information, IP address, pages visited, and time spent on the website, collected automatically through cookies and similar technologies.
            </li>
        </ul>
    </section>

    <section>
        <h2>2. How We Use Your Information</h2>
        <p>We use the collected information to:</p>
        <ul>
            <li>Provide, operate, and maintain our website and services.</li>
            <li>Process transactions and send notifications.</li>
            <li>Personalize user experience and analyze website performance.</li>
            <li>Comply with legal obligations and prevent fraudulent activities.</li>
        </ul>
    </section>

    <section>
        <h2>3. Cookies and Tracking Technologies</h2>
        <p>
            We use cookies, web beacons, and similar technologies to improve website functionality and enhance your experience.
            You can manage your cookie preferences in your browser settings.
        </p>
    </section>

    <section>
        <h2>4. Sharing Your Information</h2>
        <p>We do not sell or rent your personal data. However, we may share information with:</p>
        <ul>
            <li>Service providers who assist in operating our website or conducting business.</li>
            <li>Legal authorities, if required by law or to protect our rights.</li>
        </ul>
    </section>

    <section>
        <h2>5. Data Security</h2>
        <p>
            We implement industry-standard security measures to protect your data. However, no online system is entirely secure,
            and we cannot guarantee absolute security.
        </p>
    </section>

    <section>
        <h2>6. Your Rights</h2>
        <p>Depending on your jurisdiction, you may have the right to:</p>
        <ul>
            <li>Access, update, or delete your personal data.</li>
            <li>Opt-out of data collection or marketing communications.</li>
        </ul>
        <p>To exercise your rights, please contact us at <a href="tel:+998{{ $phone }}">+998{{ $phone }}</a>.</p>
    </section>

    <section>
        <h2>7. Third-Party Links</h2>
        <p>
            Our website may contain links to external sites. We are not responsible for the privacy practices of these websites.
            Please review their privacy policies separately.
        </p>
    </section>

    <section>
        <h2>8. Changes to This Policy</h2>
        <p>
            We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date.
        </p>
    </section>

    <section>
        <h2>9. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us:</p>
        <ul>
            <li>
                Phone: +998{{ $phone }}
            </li>
            <li>
                Address: {{ $address }}
            </li>
        </ul>
    </section>
</body>
</html>
