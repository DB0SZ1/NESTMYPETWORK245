<?php
$pageTitle = "NestMyPet | Information";
require 'db.php';
include 'header.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - NestMyPet</title>
    
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Load Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Configure Tailwind with NestMyPet brand colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        // Based on the logo and previous footer
                        'nest-blue': {
                            '700': '#3A4B68',
                            '800': '#2d3748',
                        },
                        'nest-orange': {
                            '500': '#F59E0B', // A vibrant, friendly orange
                            '600': '#D97706',
                        },
                        'nest-green': {
                            '500': '#84CC16', // A bright, welcoming green
                            '600': '#65A30D',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* A little extra polish for form inputs */
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            border-color: #F59E0B;
            box-shadow: 0 0 0 2px #F59E0B;
        }
    </style>
</head>

<body class="font-sans bg-slate-50">



    <!-- 
      Main Contact Section 
    -->
    <main>
        <div class="relative bg-slate-50">
            <!-- Hero Introduction -->
            <div class="text-center max-w-3xl mx-auto pt-16 pb-12 px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-nest-blue-800">
                    Get in Touch
                </h1>
                <p class="mt-6 text-xl text-slate-600">
                    Have a question, a suggestion, or just want to say hi? We'd love to hear from you. Fill out the form below or use one of our other contact channels.
                </p>
            </div>

            <!-- 
              Main Contact Card
              A split-layout card with info on the left and form on the right.
            -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden lg:grid lg:grid-cols-5">
                    
                    <!-- Left Side: Contact Info & Other Channels -->
                    <div class="lg:col-span-2 bg-slate-50 p-8 sm:p-12">
                        <h2 class="text-3xl font-bold text-nest-blue-800 mb-6">Contact Information</h2>
                        <p class="text-lg text-slate-600 mb-8">
                            Before you reach out, you might find a quick answer on our FAQ page!
                        </p>
                        
                        <ul class="space-y-6">
                            <!-- FAQ -->
                            <li class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="flex items-center justify-center h-12 w-12 rounded-lg bg-nest-orange-500 bg-opacity-10">
                                        <!-- Icon: Lifebuoy/Support -->
                                        <svg class="h-6 w-6 text-nest-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-nest-blue-800">Visit our FAQ</h3>
                                    <p class="mt-1 text-base text-slate-600">Find quick answers to common questions about booking, hosting, and pet safety.</p>
                                    <a href="#" class="mt-2 text-base font-medium text-nest-orange-600 hover:text-nest-orange-500">
                                        Browse FAQs &rarr;
                                    </a>
                                </div>
                            </li>
                            
                            <!-- Email -->
                            <li class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="flex items-center justify-center h-12 w-12 rounded-lg bg-nest-green-500 bg-opacity-10">
                                        <!-- Icon: Envelope/Email -->
                                        <svg class="h-6 w-6 text-nest-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-nest-blue-800">Email Us Directly</h3>
                                    <p class="mt-1 text-base text-slate-600">For specific inquiries, feel free to send us an email. We typically respond within 24 hours.</p>
                                    <a href="mailto:support@nestmypet.com" class="mt-2 text-base font-medium text-nest-green-600 hover:text-nest-green-500">
                                        support@nestmypet.com
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Right Side: Contact Form -->
                    <div class="lg:col-span-3 p-8 sm:p-12">
                        <h2 class="text-3xl font-bold text-nest-blue-800 mb-6">Send us a message</h2>
                        
                        <form id="contact-form" class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                            <!-- First Name -->
                            <div>
                                <label for="first-name" class="block text-sm font-medium text-slate-700">First name</label>
                                <div class="mt-1">
                                    <input type="text" name="first-name" id="first-name" autocomplete="given-name" required class="block w-full rounded-lg border-slate-300 shadow-sm py-3 px-4 transition">
                                </div>
                            </div>
                            
                            <!-- Last Name -->
                            <div>
                                <label for="last-name" class="block text-sm font-medium text-slate-700">Last name</label>
                                <div class="mt-1">
                                    <input type="text" name="last-name" id="last-name" autocomplete="family-name" required class="block w-full rounded-lg border-slate-300 shadow-sm py-3 px-4 transition">
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="sm:col-span-2">
                                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                                <div class="mt-1">
                                    <input id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-lg border-slate-300 shadow-sm py-3 px-4 transition">
                                </div>
                            </div>
                            
                            <!-- Subject -->
                            <div class="sm:col-span-2">
                                <label for="subject" class="block text-sm font-medium text-slate-700">Subject</label>
                                <div class="mt-1">
                                    <input type="text" name="subject" id="subject" required class="block w-full rounded-lg border-slate-300 shadow-sm py-3 px-4 transition">
                                </div>
                            </div>
                            
                            <!-- Message -->
                            <div class="sm:col-span-2">
                                <label for="message" class="block text-sm font-medium text-slate-700">Message</label>
                                <div class="mt-1">
                                    <textarea id="message" name="message" rows="4" required class="block w-full rounded-lg border-slate-300 shadow-sm py-3 px-4 transition"></textarea>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="sm:col-span-2">
                                <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-nest-orange-500 hover:bg-nest-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-nest-orange-500 transition-colors">
                                    Send Message
                                </button>
                            </div>
                        </form>
                        
                        <!-- Success/Error Message Box -->
                        <div id="message-box" class="mt-6 hidden">
                            <div class="rounded-md bg-green-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">Message Sent!</h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>Thanks for reaching out. We'll get back to you as soon as possible!</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </main>


      
    <script>
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            // Prevent the form from actually submitting
            e.preventDefault();
            
            // Get the message box
            const messageBox = document.getElementById('message-box');
            
            // Show the success message
            messageBox.classList.remove('hidden');
            
            // Optional: reset the form
            e.target.reset();
            
            // Optional: hide the message after a few seconds
            setTimeout(() => {
                messageBox.classList.add('hidden');
            }, 5000);
        });
    </script>

</body>
</html>

<?php
include 'footer.php';
?>