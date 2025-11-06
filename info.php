<?php
$pageTitle = "NestMyPet | Information";
require 'db.php';
include 'header.php';
?>

<main>
    <section class="info-page">
        <div class="container">
            <!-- Dynamic Content Container -->
            <div id="info-content" class="info-content-wrapper">
                <!-- Content will be loaded here dynamically -->
            </div>
        </div>
    </section>
</main>

<style>
.info-page {
    padding: 3rem 0;
    min-height: 60vh;
    background-color: var(--light-grey-bg);
}

.info-content-wrapper {
    background-color: var(--white-color);
    border-radius: var(--border-radius);
    padding: 3rem;
    box-shadow: var(--box-shadow);
    max-width: 900px;
    margin: 0 auto;
}

.info-content-wrapper h1 {
    font-size: 2.5rem;
    color: var(--dark-color);
    margin-bottom: 1.5rem;
    border-bottom: 3px solid var(--primary-color);
    padding-bottom: 1rem;
}

.info-content-wrapper h2 {
    font-size: 1.8rem;
    color: var(--dark-color);
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.info-content-wrapper h3 {
    font-size: 1.4rem;
    color: var(--dark-color);
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.info-content-wrapper h4 {
    font-size: 1.2rem;
    color: var(--dark-color);
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.info-content-wrapper p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--text-color);
    margin-bottom: 1rem;
}

.info-content-wrapper ul,
.info-content-wrapper ol {
    margin-left: 2rem;
    margin-bottom: 1.5rem;
}

.info-content-wrapper li {
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--text-color);
    margin-bottom: 0.75rem;
}

.info-content-wrapper strong {
    font-weight: 600;
    color: var(--dark-color);
}

.info-content-wrapper a {
    color: var(--secondary-color);
    font-weight: 600;
}

.info-content-wrapper a:hover {
    text-decoration: underline;
}

.faq-item {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-question {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.faq-answer {
    color: var(--text-color);
    line-height: 1.8;
}

.section-group {
    margin-bottom: 2.5rem;
}

.section-group:last-child {
    margin-bottom: 0;
}

.loading {
    text-align: center;
    padding: 3rem;
    color: var(--text-color);
}

.error-message {
    background-color: #fee;
    border: 1px solid #fcc;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    color: #c33;
    text-align: center;
}

@media (max-width: 768px) {
    .info-content-wrapper {
        padding: 2rem 1.5rem;
    }
    
    .info-content-wrapper h1 {
        font-size: 2rem;
    }
}
</style>

<script>
// Content Database - All information extracted from documents
const contentDatabase = {
    faqs: {
        title: "Frequently Asked Questions",
        sections: [
            {
                category: "🏡 GENERAL QUESTIONS",
                items: [
                    {
                        question: "What is NestMyPet?",
                        answer: "NestMyPet is a UK-based pet sitting and home boarding platform that connects loving, local hosts with pet owners who need reliable care. Whether you're going away for a weekend or a month, NestMyPet helps you find someone who will treat your pet like family — in a real home."
                    },
                    {
                        question: "How does NestMyPet work?",
                        answer: "It's simple: Search for verified local hosts, connect and chat, book securely through our platform, and relax knowing your pet is safe and loved."
                    },
                    {
                        question: "What types of pets can I book care for?",
                        answer: "NestMyPet welcomes dogs, cats, rabbits, guinea pigs, hamsters, and other small animals. Each sitter lists what types of pets they accept on their profile."
                    },
                    {
                        question: "Is NestMyPet available across the UK?",
                        answer: "Yes! We're building a nationwide community of trusted pet carers. If you can't find a sitter yet in your area, join our waitlist and we'll notify you."
                    },
                    {
                        question: "What makes NestMyPet different from kennels or catteries?",
                        answer: "NestMyPet offers home-based, personalised care — not cages. Pets stay in real homes with tailored routines and care."
                    },
                    {
                        question: "How do I contact customer support?",
                        answer: "You can reach us anytime at support@nestmypet.co.uk or via the Contact Us form. Emergency support is available 24/7 for active bookings."
                    },
                    {
                        question: "How do I know my sitter is trustworthy?",
                        answer: "All sitters go through ID verification, profile review, and optional DBS checks. Reviews from verified owners are displayed on every sitter's profile."
                    }
                ]
            },
            {
                category: "🐶 PET OWNER FAQs",
                items: [
                    {
                        question: "How do I find a pet sitter near me?",
                        answer: "Enter your postcode to view nearby hosts and filter by pet type, location, and availability."
                    },
                    {
                        question: "How do I book a sitter or host?",
                        answer: "Once you've found someone suitable, message them to discuss your needs, then confirm through NestMyPet's secure checkout."
                    },
                    {
                        question: "Can I meet my sitter before confirming a booking?",
                        answer: "Yes! We encourage a meet and greet — either in person or via video call — before booking."
                    },
                    {
                        question: "What happens if my sitter cancels?",
                        answer: "If your sitter cancels, the NestMyPet Guarantee provides a full refund or credit towards another sitter."
                    },
                    {
                        question: "How do payments work?",
                        answer: "All payments are handled securely through Stripe. Sitters are paid after the booking ends."
                    },
                    {
                        question: "Can I message my sitter before booking?",
                        answer: "Yes, you can message sitters directly via our in-platform chat to discuss your pet's needs."
                    },
                    {
                        question: "What happens if my pet becomes unwell while I'm away?",
                        answer: "The sitter will contact you and your vet immediately. The NestMyPet Guarantee may cover emergency treatment costs."
                    },
                    {
                        question: "Can I book a sitter to stay in my own home?",
                        answer: "Yes. Sitters offering in-home care are marked as such and must complete basic DBS checks."
                    }
                ]
            },
            {
                category: "🏠 SITTER / HOST FAQs",
                items: [
                    {
                        question: "How do I become a NestMyPet host?",
                        answer: "Sign up as a host, complete ID verification, optional DBS, empathy training, and upload home photos."
                    },
                    {
                        question: "Do I need experience with animals?",
                        answer: "No formal training required, but you should have confidence around animals. We offer online guidance."
                    },
                    {
                        question: "How does payment work for sitters?",
                        answer: "Hosts receive payment via Stripe after each booking, minus NestMyPet's 10% commission."
                    },
                    {
                        question: "Can I choose which pets to accept?",
                        answer: "Yes. You can specify preferred pet types and sizes when setting up your listing."
                    },
                    {
                        question: "Are sitters covered by the NestMyPet Guarantee?",
                        answer: "Yes. Sitters are covered for third-party property damage and protected by the booking policy."
                    }
                ]
            },
            {
                category: "💳 PAYMENTS & SECURITY",
                items: [
                    {
                        question: "Is payment secure?",
                        answer: "Yes. NestMyPet uses Stripe for all transactions."
                    },
                    {
                        question: "Can I pay or receive cash?",
                        answer: "No. All payments must go through the platform for safety and protection."
                    },
                    {
                        question: "How much commission does NestMyPet take?",
                        answer: "NestMyPet takes a 10% commission from each confirmed booking."
                    },
                    {
                        question: "What payment methods are accepted?",
                        answer: "All major debit and credit cards, Apple Pay, and Google Pay."
                    }
                ]
            },
            {
                category: "🔒 VERIFICATION & SAFETY",
                items: [
                    {
                        question: "How do you verify sitters?",
                        answer: "Sitters undergo ID checks, profile review, safety training, and optional DBS screening."
                    },
                    {
                        question: "What is the NestMyPet Guarantee?",
                        answer: "A discretionary coverage for vet care (up to £500), property damage (up to £250), and booking protection."
                    },
                    {
                        question: "How do I report a safety concern?",
                        answer: "Contact support@nestmypet.co.uk immediately. We aim to respond within 24 hours."
                    }
                ]
            }
        ]
    },
    
    about: {
        title: "About NestMyPet",
        subtitle: "Where Every Pet Feels at Home",
        content: `
            <p>At NestMyPet, we believe pets deserve more than just care — they deserve connection. We were created for owners who want to know their pets are not only safe, but understood and loved while they're away. When you choose NestMyPet, you're choosing gentle, personal care from trusted local sitters who treat your pets like family. Whether you're gone for a night, a week, or more, we make every stay feel like home.</p>

            <h2>Our Story</h2>
            <p>NestMyPet began with a simple realisation — that leaving a pet behind can be one of the hardest parts of travel. Many of us have felt the worry: Will they be comfortable? Will they eat? Will they feel loved? We noticed that while kennels and catteries could meet practical needs, something deeper was missing — the warmth and emotional connection pets thrive on.</p>
            
            <p>So, we set out to build a community that put empathy first. NestMyPet was founded to bridge the gap between loving pet owners and compassionate local sitters who care with heart. We wanted to give people peace of mind, knowing their pets are safe, content, and seen — not just looked after.</p>
            
            <p>Today, NestMyPet is more than a service — it's a movement of kindness, where pets feel at home, and owners feel understood.</p>

            <h2>Our Mission</h2>
            <p>To connect pet owners with verified, compassionate sitters who offer safe, home-based care that gives complete peace of mind and keeps tails wagging and hearts at ease. We believe that when people care with empathy, honesty, and understanding — pets thrive.</p>

            <h2>Why We Exist</h2>
            <p>Many pet owners told us the same thing: 'I just want someone kind and reliable who will love my pet like I do.' That's why NestMyPet was born — to give every pet a second home and every owner a sense of calm. We combine personal connection with professional safeguards, so you can relax knowing your pet's wellbeing is always our priority.</p>

            <h2>What Makes NestMyPet Different</h2>
            <ul>
                <li><strong>💛 Loving, Local Sitters</strong> — Our sitters are real people in your community — caring, vetted, and ready to give your pet a warm, homely experience.</li>
                <li><strong>🏡 Home-from-Home Comfort</strong> — No cages or cold kennels — just cuddles, care, and comfort. Your pet stays in a safe, calm home environment where routines and personalities are respected.</li>
                <li><strong>🩺 Peace of Mind Guarantee</strong> — Every verified booking includes protection for emergencies, sitter cancellations, or accidental damage. We call it the NestMyPet Guarantee — your reassurance that care always comes first.</li>
                <li><strong>🧩 Verified & Reviewed Sitters</strong> — Each sitter completes identity checks and safety steps, with the option for a basic DBS check for in-home bookings. Every sitter is reviewed by real pet owners.</li>
                <li><strong>💬 Real Updates, Real Connection</strong> — Stay close, even when you're away. Sitters send regular photos and updates, so you always feel connected to your furry friend.</li>
            </ul>

            <h2>Our Promise</h2>
            <p>We promise that your pet's comfort, safety, and happiness will always come first. Our sitters are chosen not just for experience, but for empathy — for the way they notice the little things that make your pet who they are. At NestMyPet, care isn't a service. It's a relationship built on trust, warmth, and understanding. That's how we make pet care feel personal again.</p>

            <h2>Connect With Us</h2>
            <p>🐕 <strong>Website:</strong> nestmypet.co.uk<br>
            📧 <strong>Email:</strong> support@nestmypet.co.uk<br>
            ☎️ <strong>24/7 Emergency Line:</strong> (coming soon)<br>
            📱 <strong>Follow us:</strong> Instagram & Facebook — @NestMyPet</p>
        `
    },
    
    safety: {
        title: "Safety & Emergency Policy",
        subtitle: "Your Pet's Safety Always Comes First",
        content: `
            <p><em>Last Updated: October 2025</em></p>

            <h2>🔒 Our Commitment to Safety</h2>
            <p>At NestMyPet, your pet's safety always comes first. We are committed to providing safe, reliable care and ensuring that sitters are prepared to respond quickly in an emergency. This policy applies to all bookings made and paid for through the NestMyPet platform.</p>
            
            <p>NestMyPet ensures:</p>
            <ul>
                <li>Verified sitters with identity checks completed</li>
                <li>Transparent sitter profiles including home environment information</li>
                <li>Communication and booking protection through our secure platform</li>
                <li>Guidance and support available throughout every booking</li>
            </ul>
            
            <p>Sitters are responsible for providing attentive, compassionate care and following all safety guidance described in this policy.</p>

            <h2>✅ Pet Owner Safety Responsibilities</h2>
            <p>To keep pets safe, owners must:</p>
            <ul>
                <li>Provide accurate and up-to-date pet information in their profile (vaccination status, behaviour history, medical needs, feeding routines)</li>
                <li>Ensure pets are microchipped and wearing ID tags where legally required</li>
                <li>Provide sufficient food, medications, and equipment for the booking</li>
                <li>Disclose any behaviours that require caution (e.g., biting, chewing, anxiety, chasing, escapes)</li>
                <li>Provide a reachable phone number and an emergency contact</li>
            </ul>

            <h2>✅ Sitter Safety Responsibilities</h2>
            <p><strong>Important:</strong> Sitters must be competent and confident to safely handle pets of the size, species, behaviours and needs they accept. If unsure, they must decline the booking.</p>
            
            <p>Sitters agree to:</p>
            <ul>
                <li>Supervise pets appropriately and never leave unfamiliar pets unattended outdoors</li>
                <li>Keep all hazardous items out of reach: cleaning products, wires, toxic foods, open windows/balconies, garden access, etc.</li>
                <li>Follow the owner's written care instructions at all times</li>
                <li>Check in regularly through the app with updates and photos</li>
                <li>Notify the owner immediately if the pet shows distress or unusual behaviour</li>
                <li>Never give medication without owner approval</li>
                <li>Use leads/harnesses when walking pets in public places</li>
            </ul>
            
            <p>Sitters must ensure a safe, secure home environment before accepting a booking.</p>

            <h2>🚑 Emergency Response Procedure</h2>
            <p>If a pet becomes ill or injured during a booking:</p>
            <ol>
                <li><strong>Assess the situation</strong> and keep the pet calm and safe</li>
                <li><strong>Contact the Pet Owner immediately</strong></li>
                <li><strong>If owner cannot be reached</strong> → contact the named emergency contact</li>
                <li><strong>If urgent care is needed</strong> → take the pet to the owner's chosen vet (or the nearest available veterinary practice, if necessary)</li>
                <li><strong>Report full details to NestMyPet Support</strong> at: <a href="mailto:support@nestmypet.co.uk">support@nestmypet.co.uk</a> (or via in-app contact)</li>
            </ol>

            <h3>Sitters must provide:</h3>
            <ul>
                <li>What happened</li>
                <li>When & where it took place</li>
                <li>Actions taken</li>
                <li>Any vet documentation/invoices</li>
            </ul>
            
            <p>We will support communication and assist if a claim may fall under the NestMyPet Guarantee.</p>

            <h2>🐾 Lost Pet Procedure</h2>
            <p><em>(Extremely rare, but important to know)</em></p>
            
            <p>If a pet goes missing:</p>
            <ul>
                <li>Notify the owner immediately</li>
                <li>Contact local dog wardens/shelters/vets if 30 minutes have passed</li>
                <li>Alert NestMyPet for guidance and support</li>
                <li>Continue searching the area safely until the situation is resolved</li>
            </ul>

            <h2>💼 Handling Behavioural Incidents</h2>
            <p>If a pet shows unsafe behaviours (aggression, escape attempts, destructive stress), sitters must:</p>
            <ul>
                <li>Prevent further risk immediately</li>
                <li>Contact the owner to agree a safe plan</li>
                <li>Document actions and communication clearly</li>
            </ul>
            
            <p>NestMyPet can help arrange alternate care if required.</p>

            <h2>📝 Failure to Comply</h2>
            <p>Anyone who fails to follow safety standards may face:</p>
            <ul>
                <li>Profile suspension</li>
                <li>Removal from the platform</li>
                <li>Potential insurance and guarantee claim restrictions</li>
            </ul>
            
            <p>We take every incident seriously to protect both pets and people.</p>

            <h2>❤️ Our Approach</h2>
            <p>Safety is shared. Trust grows by:</p>
            <ul>
                <li>Honest communication</li>
                <li>Clear instructions</li>
                <li>Quick responses</li>
                <li>Caring from the heart</li>
            </ul>
            
            <p><strong>At NestMyPet, we believe every pet deserves to feel safe, secure and loved — always.</strong></p>
        `
    },
    
    contact: {
        title: "Contact Us",
        content: `
            <p>We're here to help! Whether you have questions about bookings, need support, or just want to say hello, we'd love to hear from you.</p>

            <h2>Get in Touch</h2>
            <ul>
                <li><strong>📧 Email:</strong> <a href="mailto:support@nestmypet.co.uk">support@nestmypet.co.uk</a></li>
                <li><strong>📞 Phone:</strong> 24/7 Emergency support available for active bookings</li>
                <li><strong>💬 Live Chat:</strong> Available through our platform during office hours</li>
            </ul>

            <h2>Office Hours</h2>
            <p>Monday - Friday: 9:00 AM - 6:00 PM (GMT)<br>
            Saturday: 10:00 AM - 4:00 PM (GMT)<br>
            Sunday: Closed (Emergency support available for active bookings)</p>

            <h2>Response Times</h2>
            <ul>
                <li><strong>General Inquiries:</strong> Within 24 hours</li>
                <li><strong>Booking Support:</strong> Within 12 hours</li>
                <li><strong>Emergency Support:</strong> Immediate (for active bookings)</li>
                <li><strong>Safety Concerns:</strong> Within 24 hours</li>
            </ul>

            <h2>Visit Us</h2>
            <p>NestMyPet Ltd<br>
            United Kingdom</p>

            <h2>Follow Us</h2>
            <p>Stay connected with NestMyPet on social media:<br>
            📱 Instagram: @NestMyPet<br>
            📘 Facebook: @NestMyPet<br>
            🦤 Twitter: @NestMyPet</p>

            <h2>Report a Safety Concern</h2>
            <p>If you have a safety concern, please contact us immediately at <a href="mailto:support@nestmypet.co.uk">support@nestmypet.co.uk</a> with "URGENT" in the subject line. We aim to respond within 24 hours.</p>
        `
    },
    
    terms: {
        title: "Terms and Conditions",
        content: `
            <p><em>Last Updated: October 2025</em></p>

            <h2>1. Introduction</h2>
            <p>Welcome to NestMyPet. By accessing or using our platform, you agree to be bound by these Terms and Conditions. Please read them carefully.</p>

            <h2>2. Platform Services</h2>
            <p>NestMyPet is a UK-based pet sitting and home boarding platform that connects pet owners with local hosts. We facilitate bookings but are not directly responsible for the care provided by individual sitters.</p>

            <h2>3. User Accounts</h2>
            <ul>
                <li>You must be at least 18 years old to create an account</li>
                <li>You are responsible for maintaining the confidentiality of your account</li>
                <li>You agree to provide accurate and complete information</li>
                <li>You must not share your account with others</li>
            </ul>

            <h2>4. Bookings and Payments</h2>
            <ul>
                <li>All payments must be processed through our secure platform using Stripe</li>
                <li>NestMyPet takes a 10% commission from each confirmed booking</li>
                <li>Payments to sitters are released after the booking ends</li>
                <li>Cash payments are strictly prohibited</li>
                <li>Cancellation policies apply as outlined in the booking agreement</li>
            </ul>

            <h2>5. Sitter Verification</h2>
            <p>All sitters undergo ID verification, profile review, and optional DBS checks. However, NestMyPet does not guarantee the behavior or performance of individual sitters. Pet owners are encouraged to meet sitters before booking.</p>

            <h2>6. NestMyPet Guarantee</h2>
            <p>The NestMyPet Guarantee provides discretionary coverage for:</p>
            <ul>
                <li>Emergency vet care (up to £500)</li>
                <li>Property damage (up to £250)</li>
                <li>Booking protection in case of sitter cancellation</li>
            </ul>
            <p>Coverage is subject to terms and conditions and is provided at NestMyPet's discretion.</p>

            <h2>7. User Responsibilities</h2>
            <h3>Pet Owners must:</h3>
            <ul>
                <li>Provide accurate information about their pet's health and behavior</li>
                <li>Ensure their pet is up to date on vaccinations</li>
                <li>Disclose any behavioral issues or medical conditions</li>
                <li>Provide emergency contact information</li>
            </ul>

            <h3>Sitters must:</h3>
            <ul>
                <li>Provide a safe and clean environment</li>
                <li>Follow the pet owner's care instructions</li>
                <li>Contact the owner immediately in case of emergency</li>
                <li>Provide regular updates and photos</li>
            </ul>

            <h2>8. Prohibited Conduct</h2>
            <p>Users must not:</p>
            <ul>
                <li>Engage in fraudulent activity</li>
                <li>Harass or threaten other users</li>
                <li>Violate any laws or regulations</li>
                <li>Attempt to bypass platform payment systems</li>
                <li>Provide false or misleading information</li>
            </ul>

            <h2>9. Liability</h2>
            <p>NestMyPet acts as an intermediary platform. We are not liable for:</p>
            <ul>
                <li>Injury or illness to pets during care</li>
                <li>Disputes between pet owners and sitters</li>
                <li>Loss or damage to personal property</li>
                <li>Actions or omissions of individual users</li>
            </ul>

            <h2>10. Termination</h2>
            <p>NestMyPet reserves the right to suspend or terminate accounts that violate these terms or engage in prohibited conduct.</p>

            <h2>11. Changes to Terms</h2>
            <p>We may update these Terms and Conditions periodically. Users will be notified of material changes via email or platform notification.</p>

            <h2>12. Governing Law</h2>
            <p>These terms are governed by the laws of England and Wales. Any disputes will be subject to the exclusive jurisdiction of the courts of England and Wales.</p>

            <h2>13. Contact</h2>
            <p>For questions about these Terms and Conditions, please contact us at <a href="mailto:support@nestmypet.co.uk">support@nestmypet.co.uk</a>.</p>
        `
    },
    
    privacy: {
        title: "Privacy Policy",
        content: `
            <p><em>Last Updated: October 2025 | GDPR Compliant</em></p>

            <h2>1. Introduction</h2>
            <p>NestMyPet is committed to protecting your privacy. This policy outlines how we collect, use, disclose, and protect your personal data in accordance with the UK General Data Protection Regulation (UK GDPR).</p>

            <h2>2. Data Controller</h2>
            <p>NestMyPet Ltd is the data controller for all personal data collected through our website, applications, and services.</p>
            <p><strong>Contact:</strong> <a href="mailto:privacy@nestmypet.com">privacy@nestmypet.com</a></p>

            <h2>3. What Data We Collect</h2>
            <p>We may collect and process the following personal data:</p>
            <ul>
                <li><strong>Identification data:</strong> name, date of birth, address, contact details</li>
                <li><strong>Verification data:</strong> photo ID, Stripe Identity outputs</li>
                <li><strong>Profile data:</strong> bio, pet details, preferences</li>
                <li><strong>Communication data:</strong> messages, support interactions</li>
                <li><strong>Transaction data:</strong> payment method, booking history</li>
                <li><strong>Technical data:</strong> IP address, device info, cookies</li>
            </ul>

            <h2>4. How We Use Your Data</h2>
            <p>Your data is used to:</p>
            <ul>
                <li>Facilitate bookings and provide platform features</li>
                <li>Verify identity and manage account security</li>
                <li>Process payments and payouts</li>
                <li>Personalise content and improve the platform</li>
                <li>Comply with legal obligations</li>
                <li>Prevent fraud and ensure safety</li>
            </ul>
            <p><strong>We do not sell your personal data to third parties.</strong></p>

            <h2>5. Legal Basis for Processing</h2>
            <p>We rely on the following lawful bases under UK GDPR:</p>
            <ul>
                <li><strong>Consent</strong> — for marketing or optional services</li>
                <li><strong>Contract</strong> — to deliver booking and platform services</li>
                <li><strong>Legal obligation</strong> — e.g., tax, fraud prevention</li>
                <li><strong>Legitimate interest</strong> — to improve service or protect users</li>
            </ul>

            <h2>6. Data Sharing and Transfers</h2>
            <p>Your data may be shared with:</p>
            <ul>
                <li>Payment processors (e.g., Stripe)</li>
                <li>Identity verification partners</li>
                <li>Hosting and analytics providers</li>
                <li>Law enforcement or regulatory agencies (if required)</li>
            </ul>
            <p>Data is stored securely within the UK or EEA where possible. Transfers outside the EEA will follow standard contractual clauses and GDPR safeguards.</p>

            <h2>7. Retention Periods</h2>
            <p>We retain data as follows:</p>
            <ul>
                <li><strong>Booking and profile data:</strong> 6 years (for tax and legal compliance)</li>
                <li><strong>Support and message logs:</strong> 2 years</li>
                <li><strong>Cookie data:</strong> 12 months (unless consent withdrawn)</li>
            </ul>
            <p>You can request deletion of your account and associated data at any time unless legal obligations require us to retain it.</p>

            <h2>8. Your Rights</h2>
            <p>Under UK GDPR, you have the right to:</p>
            <ul>
                <li>Access your data</li>
                <li>Rectify inaccurate data</li>
                <li>Request erasure ("right to be forgotten")</li>
                <li>Restrict or object to processing</li>
                <li>Port your data</li>
                <li>Withdraw consent at any time</li>
            </ul>
            <p>Requests can be made by emailing <a href="mailto:privacy@nestmypet.com">privacy@nestmypet.com</a>.</p>

            <h2>9. Cookies and Analytics</h2>
            <p>Our platform uses cookies to provide essential functionality, gather usage insights, and improve service. You can manage cookie preferences through your browser or our cookie consent tool.</p>
            <p>See our Cookie Policy for details.</p>

            <h2>10. Data Security</h2>
            <p>We use industry-standard encryption and security protocols to protect data in transit and at rest. Staff and third parties are bound by confidentiality and data protection agreements.</p>

            <h2>11. Updates and Contact</h2>
            <p>We may update this policy periodically. Users will be notified of material changes via email or in-app messaging.</p>
            <p>For questions or concerns, contact: <a href="mailto:privacy@nestmypet.com">privacy@nestmypet.com</a></p>
        `
    },
    
    cookies: {
        title: "Cookie Policy",
        content: `
            <p><em>Last Updated: October 2025</em></p>

            <h2>1. What Are Cookies?</h2>
            <p>Cookies are small text files stored on your device when you visit our website. They help us provide you with a better experience by remembering your preferences and understanding how you use our platform.</p>

            <h2>2. Types of Cookies We Use</h2>
            
            <h3>Essential Cookies</h3>
            <p>These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and accessibility.</p>
            <ul>
                <li>Session management</li>
                <li>Authentication</li>
                <li>Load balancing</li>
            </ul>

            <h3>Analytical/Performance Cookies</h3>
            <p>These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously.</p>
            <ul>
                <li>Google Analytics</li>
                <li>Page visit tracking</li>
                <li>User behavior analysis</li>
            </ul>

            <h3>Functionality Cookies</h3>
            <p>These cookies enable the website to provide enhanced functionality and personalization.</p>
            <ul>
                <li>Language preferences</li>
                <li>Region selection</li>
                <li>User interface customization</li>
            </ul>

            <h3>Targeting/Advertising Cookies</h3>
            <p>These cookies may be set through our site by our advertising partners to build a profile of your interests.</p>
            <ul>
                <li>Personalized advertisements</li>
                <li>Marketing campaign effectiveness</li>
                <li>Social media integration</li>
            </ul>

            <h2>3. How We Use Cookies</h2>
            <ul>
                <li>To remember your login details</li>
                <li>To understand how you use our platform</li>
                <li>To improve our services based on your preferences</li>
                <li>To personalize content and advertisements</li>
                <li>To ensure the security of your account</li>
            </ul>

            <h2>4. Managing Cookies</h2>
            <p>You can control and manage cookies in several ways:</p>

            <h3>Browser Settings</h3>
            <p>Most browsers allow you to:</p>
            <ul>
                <li>View what cookies are stored and delete them individually</li>
                <li>Block third-party cookies</li>
                <li>Block cookies from specific sites</li>
                <li>Block all cookies</li>
                <li>Delete all cookies when you close your browser</li>
            </ul>

            <h3>Our Cookie Consent Tool</h3>
            <p>When you first visit NestMyPet, you'll see a cookie consent banner. You can:</p>
            <ul>
                <li>Accept all cookies</li>
                <li>Reject non-essential cookies</li>
                <li>Customize your preferences</li>
            </ul>

            <h2>5. Third-Party Cookies</h2>
            <p>Some cookies on our site are set by third-party services:</p>
            <ul>
                <li><strong>Google Analytics:</strong> For website traffic analysis</li>
                <li><strong>Stripe:</strong> For secure payment processing</li>
                <li><strong>Social Media Platforms:</strong> For social sharing features</li>
            </ul>

            <h2>6. Cookie Retention</h2>
            <p>Cookies are retained for the following periods:</p>
            <ul>
                <li><strong>Session Cookies:</strong> Deleted when you close your browser</li>
                <li><strong>Persistent Cookies:</strong> Up to 12 months</li>
                <li><strong>Analytics Cookies:</strong> Up to 24 months</li>
            </ul>

            <h2>7. Impact of Disabling Cookies</h2>
            <p>If you disable cookies:</p>
            <ul>
                <li>You may not be able to use all features of our platform</li>
                <li>Your user experience may be affected</li>
                <li>You may need to re-enter information more frequently</li>
            </ul>

            <h2>8. Updates to This Policy</h2>
            <p>We may update this Cookie Policy from time to time. When we make changes, we'll update the "Last Updated" date at the top of this page.</p>

            <h2>9. Contact Us</h2>
            <p>If you have questions about our use of cookies, please contact us at <a href="mailto:privacy@nestmypet.com">privacy@nestmypet.com</a>.</p>
        `
    },
    
    help: {
        title: "Help Center",
        content: `
            <p>Welcome to the NestMyPet Help Center. Here you'll find answers to common questions and guidance on using our platform.</p>

            <h2>Getting Started</h2>
            
            <h3>For Pet Owners</h3>
            <ol>
                <li><strong>Create an Account:</strong> Sign up using your email and create a password</li>
                <li><strong>Add Your Pet:</strong> Complete your pet's profile with photos and important information</li>
                <li><strong>Search for Sitters:</strong> Enter your postcode and dates to find available hosts</li>
                <li><strong>Connect & Chat:</strong> Message sitters to discuss your pet's needs</li>
                <li><strong>Book Securely:</strong> Complete your booking through our secure platform</li>
                <li><strong>Stay Connected:</strong> Receive updates and photos while you're away</li>
            </ol>

            <h3>For Sitters</h3>
            <ol>
                <li><strong>Sign Up as a Host:</strong> Create your sitter profile</li>
                <li><strong>Complete Verification:</strong> Upload ID and complete safety checks</li>
                <li><strong>Set Your Availability:</strong> Choose when you're available to host pets</li>
                <li><strong>Set Your Rates:</strong> Determine your pricing per night</li>
                <li><strong>Receive Bookings:</strong> Connect with pet owners in your area</li>
                <li><strong>Provide Great Care:</strong> Send regular updates and photos</li>
            </ol>

            <h2>Booking Management</h2>
            
            <h3>How to Modify a Booking</h3>
            <p>Contact your sitter or pet owner directly through our messaging system. For significant changes, you may need to cancel and create a new booking.</p>

            <h3>Cancellation Policy</h3>
            <ul>
                <li><strong>More than 7 days before:</strong> Full refund</li>
                <li><strong>3-7 days before:</strong> 50% refund</li>
                <li><strong>Less than 3 days:</strong> No refund (exceptions may apply)</li>
            </ul>

            <h3>If Your Sitter Cancels</h3>
            <p>You'll receive either a full refund or credit toward another booking through the NestMyPet Guarantee.</p>

            <h2>Safety & Trust</h2>
            
            <h3>Meet and Greet</h3>
            <p>We strongly recommend arranging a meet and greet (in-person or video call) before your first booking. This helps ensure compatibility between your pet and the sitter.</p>

            <h3>Emergency Procedures</h3>
            <p>If an emergency occurs during a booking:</p>
            <ol>
                <li>Sitter contacts the pet owner immediately</li>
                <li>Sitter contacts the designated vet if needed</li>
                <li>Sitter documents all actions taken</li>
                <li>Contact NestMyPet support for assistance</li>
            </ol>

            <h3>Reporting Issues</h3>
            <p>To report a problem:</p>
            <ul>
                <li>Email: <a href="mailto:support@nestmypet.co.uk">support@nestmypet.co.uk</a></li>
                <li>Use the "Report Issue" button in your booking details</li>
                <li>For urgent safety concerns, mark your email as "URGENT"</li>
            </ul>

            <h2>Payment Issues</h2>
            
            <h3>Payment Failed</h3>
            <p>If your payment fails:</p>
            <ul>
                <li>Check your card details are correct</li>
                <li>Ensure you have sufficient funds</li>
                <li>Contact your bank to verify the transaction isn't blocked</li>
                <li>Try a different payment method</li>
            </ul>

            <h3>Refund Processing</h3>
            <p>Refunds typically take 5-10 business days to appear in your account, depending on your bank.</p>

            <h3>For Sitters: Payment Schedule</h3>
            <p>Payments are released 24 hours after the booking ends, minus the 10% platform commission.</p>

            <h2>Account Management</h2>
            
            <h3>Updating Your Profile</h3>
            <p>Go to Settings > Profile to update your information, photos, and preferences.</p>

            <h3>Changing Your Password</h3>
            <p>Navigate to Settings > Security to change your password.</p>

            <h3>Deleting Your Account</h3>
            <p>Contact <a href="mailto:privacy@nestmypet.com">privacy@nestmypet.com</a> to request account deletion. We'll retain certain data as required by law.</p>

            <h2>Technical Support</h2>
            
            <h3>Can't Log In?</h3>
            <ul>
                <li>Click "Forgot Password" to reset your password</li>
                <li>Clear your browser cache and cookies</li>
                <li>Try a different browser</li>
                <li>Contact support if issues persist</li>
            </ul>

            <h3>App Issues</h3>
            <ul>
                <li>Ensure you have the latest version installed</li>
                <li>Check your internet connection</li>
                <li>Restart the app</li>
                <li>Reinstall if problems continue</li>
            </ul>

            <h2>Still Need Help?</h2>
            <p>If you can't find the answer you're looking for:</p>
            <ul>
                <li>📧 Email us at <a href="mailto:support@nestmypet.co.uk">support@nestmypet.co.uk</a></li>
                <li>💬 Use our live chat during office hours (Mon-Fri, 9 AM - 6 PM GMT)</li>
                <li>📖 Visit our <a href="info.php?page=faqs">FAQs page</a> for more detailed answers</li>
            </ul>
        `
    }
};

// Function to load content based on page parameter
function loadContent(page) {
    const contentContainer = document.getElementById('info-content');
    
    // Show loading state
    contentContainer.innerHTML = '<div class="loading"><p>Loading...</p></div>';
    
    // Simulate slight delay for smooth transition
    setTimeout(() => {
        const pageData = contentDatabase[page];
        
        if (!pageData) {
            contentContainer.innerHTML = `
                <div class="error-message">
                    <h2>Page Not Found</h2>
                    <p>Sorry, the page you're looking for doesn't exist.</p>
                    <p><a href="info.php?page=faqs">Go to FAQs</a> | <a href="index.php">Return to Home</a></p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        // Handle FAQs with special formatting
        if (page === 'faqs') {
            html += `<h1>${pageData.title}</h1>`;
            pageData.sections.forEach(section => {
                html += `<div class="section-group">`;
                html += `<h2>${section.category}</h2>`;
                section.items.forEach(item => {
                    html += `
                        <div class="faq-item">
                            <div class="faq-question">${item.question}</div>
                            <div class="faq-answer">${item.answer}</div>
                        </div>
                    `;
                });
                html += `</div>`;
            });
        } 
        // Handle About and Safety pages with subtitle
        else if (page === 'about' || page === 'safety') {
            html += `<h1>${pageData.title}</h1>`;
            if (pageData.subtitle) {
                html += `<p style="font-size: 1.3rem; color: var(--secondary-color); font-weight: 600; text-align: center; margin-bottom: 2rem;">${pageData.subtitle}</p>`;
            }
            html += pageData.content;
        }
        // Handle all other pages
        else {
            html += `<h1>${pageData.title}</h1>`;
            html += pageData.content;
        }
        
        contentContainer.innerHTML = html;
        
        // Scroll to top smoothly
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 300);
}

// Get page parameter from URL
const urlParams = new URLSearchParams(window.location.search);
const page = urlParams.get('page') || 'faqs'; // Default to FAQs if no page specified

// Load the appropriate content
loadContent(page);
</script>

<?php
include 'footer.php';
?>