<?php
// Our simple "database" of blog posts.
// To add a new post, just copy one of the array blocks and give it a new ID.
$blog_posts = [
    '1' => [
        'id' => 1,
        'title' => 'Breed Spotlight: Labrador Retriever',
        'image' => 'https://placehold.co/400x250/fdf3e6/333?text=Labrador',
        'snippet' => 'Few breeds are as popular and beloved as the Labrador Retriever. Known for their friendly nature, intelligence, and boundless energy, Labs are a top choice...',
        'content' => '
            <p>Few breeds are as popular and beloved as the Labrador Retriever. Known for their friendly, outgoing nature, intelligence, and boundless energy, Labs are a top choice for families and active individuals alike.</p>
            
            <h2>Temperament</h2>
            <p>The Labrador\'s temperament is its defining characteristic. They are famously friendly, cheerful, and kind to everyone they meet—strangers, children, and other pets included. They are eager to please, which makes them highly trainable and excellent service dogs.</p>
            
            <h2>Exercise Needs</h2>
            <p>This is a high-energy breed. A Labrador needs significant daily exercise to stay happy and healthy. They love long, brisk walks, running, and especially swimming—they were bred to retrieve from water! Without enough activity, they can become bored and may exhibit destructive behaviors.</p>
            
            <h2>Trainability</h2>
            <p>Labs are highly intelligent and live to please their owners. This combination makes them one of the most trainable breeds. They excel in obedience, agility, and, of course, retrieving. Early socialization and consistent, positive reinforcement training are key to nurturing a well-behaved companion.</p>
        '
    ],
    '2' => [
        'id' => 2,
        'title' => 'Creative DIY Pet Projects to Bond with Your Furry Friend',
        'image' => 'https://placehold.co/400x250/e8f5e9/333?text=DIY+Pet+Toy',
        'snippet' => 'DIY projects are not only fun and affordable but also a great way to strengthen the bond you share with your furry friend. [cite: 29]',
        'content' => '
            <p>Our pets bring endless joy to our lives, and what better way to give back than by creating something special just for them? [cite: 28] DIY projects are not only fun and affordable but also a great way to strengthen the bond you share with your furry friend. [cite: 29] Here are five creative projects you can try at home. [cite: 30]</p>
            
            <h2>DIY Toy 1 – Homemade Tug Rope</h2>
            <p>Have an old T-shirt or fleece blanket lying around? Cut it into strips, braid them tightly together, and knot the ends. [cite: 32] You’ll have a sturdy tug toy that’s perfect for playtime with your dog. [cite: 33]</p>
            
            <h2>DIY Toy 2 – Cardboard Puzzle Feeder</h2>
            <p>Turn an empty cardboard box into a puzzle feeder by cutting small holes in it and hiding treats inside. [cite: 36] It’s a brilliant way to provide mental stimulation, especially for indoor pets. [cite: 38]</p>

            <h2>DIY Toy 3 – Treat Jar Puzzle</h2>
            <p>Recycle a plastic jar with a secure lid. Poke a few small holes in it (big enough for kibble to fall out but not too easily). [cite: 40] Fill it with dry treats or biscuits, and watch your pet roll it around for tasty surprises. [cite: 41]</p>

            <h2>DIY Toy 4 – Catnip Sock Toy (for cats)</h2>
            <p>Take a clean sock, fill it with dried catnip, and tie it off securely. [cite: 43] This simple project is always a hit with cats—just make sure to supervise play. [cite: 44]</p>

            <h2>DIY Project 5 – Blanket Fort ‘Den’</h2>
            <p>Sometimes the simplest projects are the best. Use old blankets and cushions to create a cosy corner or fort in your living room. [cite: 46] Dogs, cats, and even small pets love a snug, quiet space where they can retreat and feel secure. [cite: 47]</p>

            <h2>Conclusion</h2>
            <p>DIY pet projects are about more than saving money—they’re about creating moments of joy and bonding with your animals. [cite: 49] Don’t forget to share photos of your creations with us on social media and tag NestMyPet! [cite: 51]</p>
            <p>🐾 [cite: 52]</p>
        '
    ],
    '3' => [
        'id' => 3,
        'title' => 'Pet Insurance: What Every Pet Owner Needs to Know',
        'image' => 'https://placehold.co/400x250/f0e9e4/333?text=Pet+Health',
        'snippet' => 'Our pets are part of the family, and just like us, they sometimes need medical care. But while we\'re fortunate to have the NHS, veterinary treatment f...',
        'content' => '
            <p>Our pets are part of the family, and just like us, they sometimes need medical care. But while we\'re fortunate to have the NHS, veterinary treatment fees can be expensive and unexpected.</p>
            
            <h2>What is Pet Insurance?</h2>
            <p>Pet insurance is a healthcare policy for your pet that helps cover the cost of veterinary bills. Similar to human health insurance, you pay a monthly premium, and the insurance company agrees to pay for a portion of your pet\'s medical costs, as defined
                in your policy.</p>
            
            <h2>Types of Policies</h2>
            <p>There are generally four main types of pet insurance:</p>
            <ul>
                <li><strong>Accident-Only:</strong> The most basic level, covering only injuries from accidents (e.g., broken bones).</li>
                <li><strong>Time-Limited:</strong> Covers both accidents and illnesses, but only for a set period (usually 12 months) after the condition first appears.</li>
                <li><strong>Maximum Benefit:</strong> Covers accidents and illnesses up to a certain financial limit per condition. Once you hit that limit, the condition is no longer covered.</li>
                <li><strong>Lifetime:</strong> The most comprehensive option. It provides a set amount of cover for all conditions each year, which resets when you renew your policy. This is ideal for long-term or chronic illnesses.</li>
            </ul>
            <p>Choosing the right policy depends on your budget, your pet\'s breed (as some are prone to certain conditions), and your peace of mind. Always read the fine print!</p>
        '
    ],
];
?>