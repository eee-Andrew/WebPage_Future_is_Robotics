(function () {
    const dropdownTriggers = document.querySelectorAll('.nav-trigger');
    const overlay = document.getElementById('product-overlay');
    const overlayBackdrop = document.getElementById('overlay-backdrop');
    const overlayClose = document.getElementById('overlay-close');
    const overlayImage = document.getElementById('overlay-image');
    const overlayTitle = document.getElementById('overlay-title');
    const overlayShort = document.getElementById('overlay-short');
    const overlayLong = document.getElementById('overlay-long');
    const overlayPrice = document.getElementById('overlay-price');
    const overlayProductIdCart = document.getElementById('overlay-product-id-cart');
    const overlayProductIdSave = document.getElementById('overlay-product-id-save');
    const overlayQuantity = document.getElementById('overlay-quantity');
    const languageToggle = document.getElementById('language-toggle');

    const translations = {
        en: {
            'nav.home': 'Home',
            'nav.who': 'Who we are',
            'nav.why': 'Why these products',
            'nav.future': 'Future is Robotics',
            'nav.prototypes': 'Prototypes',
            'nav.preschool': 'Preschool',
            'nav.primary': 'Primary',
            'nav.highschool': 'High School',
            'nav.university': 'University',
            'nav.resources': 'Resources',
            'nav.saved': 'Saved items',
            'nav.cart': 'Cart',
            'nav.contact': 'Contact us',
            'nav.account': 'Account',
            'nav.cartShort': 'Cart',
            'nav.logout': 'Logout',
            'hero.title': 'Future of Robotics Starts Here',
            'hero.subtitle': 'Interactive kits, challenges, and community—all in one place.',
            'hero.cta': 'Explore kits',
            'who.title': 'Who we are',
            'who.text': 'We are a collective of educators, engineers, and designers delivering robotics journeys for every age. Our studios build real-world challenges that help learners imagine, prototype, and launch ideas that matter.',
            'why.title': 'Why these products',
            'why.text': 'Each kit is engineered with modular parts, video guidance, and classroom-ready lesson paths. Families, schools, and makerspaces can expand or customise the builds without starting from scratch.',
            'future.title': 'Future is Robotics',
            'future.text': 'Robotics unlocks creative confidence, problem solving, and collaboration. Our community shares monthly missions, live workshops, and research briefs so every builder can keep learning.',
            'catalog.title': 'Explore our prototypes',
            'catalog.subtitle': 'Select a category below to open detailed layers for each kit, add them to your cart, or save them for later.',
            'category.preschool.title': 'Preschool prototypes',
            'category.preschool.description': 'Gentle builds that introduce motion, color, and cause-and-effect for the youngest makers.',
            'category.primary.title': 'Primary school innovators',
            'category.primary.description': 'Curated challenges that blend storytelling with hands-on robotics for growing explorers.',
            'category.highschool.title': 'High school innovators',
            'category.highschool.description': 'Rigorous builds that sharpen engineering judgment, teamwork, and experimentation.',
            'category.university.title': 'University research labs',
            'category.university.description': 'Advanced systems that dive into autonomy, feedback loops, and mission readiness.',
            'product.inStock': '{count} in stock',
            'product.empty': 'No products available in this category yet.',
            'overlay.quantity': 'Quantity',
            'overlay.addCart': 'Add to cart',
            'overlay.save': 'Save to account',
            'overlay.loginPrompt': 'Log in to add this kit to your cart or save it for later.',
            'overlay.login': 'Log in',
            'auth.loginTitle': 'Sign in to your account',
            'auth.email': 'Email',
            'auth.password': 'Password',
            'auth.loginButton': 'Log in',
            'auth.registerPromptText': 'Need an account?',
            'auth.registerLink': 'Create one',
            'auth.registerTitle': 'Create your account',
            'auth.name': 'Full name',
            'auth.confirm': 'Confirm password',
            'auth.registerButton': 'Create account',
            'auth.loginPromptText': 'Already have an account?',
            'auth.loginLink': 'Log in',
            'account.title': 'Saved items',
            'account.subtitle': 'Revisit kits you have set aside and add them to your cart when you are ready.',
            'account.empty': 'You have not saved any products yet.',
            'account.addCart': 'Add to cart',
            'account.remove': 'Remove',
            'cart.title': 'Your cart',
            'cart.empty': 'Your cart is currently empty.',
            'cart.quantity': 'Qty',
            'cart.update': 'Update',
            'cart.remove': 'Remove',
            'cart.total': 'Total',
            'checkout.title': 'Checkout',
            'checkout.name': 'Full name',
            'checkout.address1': 'Address line 1',
            'checkout.address2': 'Address line 2',
            'checkout.city': 'City',
            'checkout.postal': 'Postal code',
            'checkout.country': 'Country',
            'checkout.email': 'Email',
            'checkout.phone': 'Phone',
            'checkout.card': 'Card number',
            'checkout.expiry': 'Expiry (MM/YY)',
            'checkout.cvv': 'CVV',
            'checkout.submit': 'Pay now',
            'footer.about': 'About',
            'footer.faq': 'FAQ',
            'footer.contact': 'Contact',
        },
        el: {
            'nav.home': 'Αρχική',
            'nav.who': 'Ποιοι είμαστε',
            'nav.why': 'Γιατί αυτά τα προϊόντα',
            'nav.future': 'Το μέλλον είναι η ρομποτική',
            'nav.prototypes': 'Πρωτότυπα',
            'nav.preschool': 'Προσχολική',
            'nav.primary': 'Δημοτικό',
            'nav.highschool': 'Λύκειο',
            'nav.university': 'Πανεπιστήμιο',
            'nav.resources': 'Πόροι',
            'nav.saved': 'Αποθηκευμένα',
            'nav.cart': 'Καλάθι',
            'nav.contact': 'Επικοινωνία',
            'nav.account': 'Λογαριασμός',
            'nav.cartShort': 'Καλάθι',
            'nav.logout': 'Αποσύνδεση',
            'hero.title': 'Το μέλλον της ρομποτικής ξεκινά εδώ',
            'hero.subtitle': 'Διαδραστικά κιτ, προκλήσεις και κοινότητα — όλα σε ένα μέρος.',
            'hero.cta': 'Ανακαλύψτε κιτ',
            'who.title': 'Ποιοι είμαστε',
            'who.text': 'Είμαστε μια ομάδα εκπαιδευτικών, μηχανικών και σχεδιαστών που δημιουργούν ταξίδια ρομποτικής για κάθε ηλικία. Τα εργαστήριά μας προσφέρουν πραγματικές προκλήσεις ώστε οι μαθητές να φαντάζονται, να πρωτοτυπούν και να υλοποιούν ιδέες με αξία.',
            'why.title': 'Γιατί αυτά τα προϊόντα',
            'why.text': 'Κάθε κιτ σχεδιάζεται με αρθρωτά μέρη, βίντεο-οδηγούς και έτοιμα μαθήματα για την τάξη. Οικογένειες, σχολεία και makerspaces μπορούν να επεκτείνουν ή να προσαρμόσουν τις κατασκευές χωρίς να ξεκινούν από την αρχή.',
            'future.title': 'Το μέλλον είναι η ρομποτική',
            'future.text': 'Η ρομποτική ενισχύει τη δημιουργικότητα, την επίλυση προβλημάτων και τη συνεργασία. Η κοινότητά μας μοιράζεται μηνιαίες αποστολές, ζωντανά εργαστήρια και ερευνητικά δελτία ώστε κάθε δημιουργός να συνεχίζει να μαθαίνει.',
            'catalog.title': 'Εξερευνήστε τα πρωτότυπά μας',
            'catalog.subtitle': 'Επιλέξτε μια κατηγορία για να δείτε λεπτομέρειες, να προσθέσετε κιτ στο καλάθι ή να τα αποθηκεύσετε.',
            'category.preschool.title': 'Πρωτότυπα προσχολικής εκπαίδευσης',
            'category.preschool.description': 'Ήπιες κατασκευές που εισάγουν την κίνηση, τα χρώματα και την αιτιότητα στους μικρότερους δημιουργούς.',
            'category.primary.title': 'Καινοτόμοι δημοτικού',
            'category.primary.description': 'Δραστηριότητες που συνδυάζουν αφήγηση και ρομποτική για εξερευνητές που μεγαλώνουν.',
            'category.highschool.title': 'Καινοτόμοι λυκείου',
            'category.highschool.description': 'Απαιτητικές κατασκευές που αναπτύσσουν κρίση μηχανικού, ομαδικότητα και πειραματισμό.',
            'category.university.title': 'Εργαστήρια πανεπιστημίου',
            'category.university.description': 'Προηγμένα συστήματα που εμβαθύνουν στην αυτονομία, στους βρόχους ανάδρασης και στην ετοιμότητα αποστολών.',
            'product.inStock': 'Διαθέσιμα {count} τεμάχια',
            'product.empty': 'Δεν υπάρχουν διαθέσιμα προϊόντα σε αυτή την κατηγορία.',
            'overlay.quantity': 'Ποσότητα',
            'overlay.addCart': 'Προσθήκη στο καλάθι',
            'overlay.save': 'Αποθήκευση στον λογαριασμό',
            'overlay.loginPrompt': 'Συνδεθείτε για να προσθέσετε το κιτ στο καλάθι ή να το αποθηκεύσετε.',
            'overlay.login': 'Σύνδεση',
            'auth.loginTitle': 'Συνδεθείτε στον λογαριασμό σας',
            'auth.email': 'Ηλεκτρονικό ταχυδρομείο',
            'auth.password': 'Κωδικός πρόσβασης',
            'auth.loginButton': 'Σύνδεση',
            'auth.registerPromptText': 'Χρειάζεστε λογαριασμό;',
            'auth.registerLink': 'Δημιουργήστε έναν',
            'auth.registerTitle': 'Δημιουργήστε λογαριασμό',
            'auth.name': 'Ονοματεπώνυμο',
            'auth.confirm': 'Επιβεβαίωση κωδικού',
            'auth.registerButton': 'Εγγραφή',
            'auth.loginPromptText': 'Έχετε ήδη λογαριασμό;',
            'auth.loginLink': 'Συνδεθείτε',
            'account.title': 'Αποθηκευμένα προϊόντα',
            'account.subtitle': 'Επιστρέψτε στα κιτ που κρατήσατε και προσθέστε τα στο καλάθι όταν είστε έτοιμοι.',
            'account.empty': 'Δεν έχετε αποθηκεύσει προϊόντα ακόμη.',
            'account.addCart': 'Προσθήκη στο καλάθι',
            'account.remove': 'Αφαίρεση',
            'cart.title': 'Το καλάθι σας',
            'cart.empty': 'Το καλάθι σας είναι άδειο.',
            'cart.quantity': 'Ποσότητα',
            'cart.update': 'Ενημέρωση',
            'cart.remove': 'Αφαίρεση',
            'cart.total': 'Σύνολο',
            'checkout.title': 'Πληρωμή',
            'checkout.name': 'Ονοματεπώνυμο',
            'checkout.address1': 'Διεύθυνση (γραμμή 1)',
            'checkout.address2': 'Διεύθυνση (γραμμή 2)',
            'checkout.city': 'Πόλη',
            'checkout.postal': 'Ταχυδρομικός κώδικας',
            'checkout.country': 'Χώρα',
            'checkout.email': 'Ηλεκτρονικό ταχυδρομείο',
            'checkout.phone': 'Τηλέφωνο',
            'checkout.card': 'Αριθμός κάρτας',
            'checkout.expiry': 'Λήξη (ΜΜ/ΕΕ)',
            'checkout.cvv': 'CVV',
            'checkout.submit': 'Πληρωμή τώρα',
            'footer.about': 'Σχετικά',
            'footer.faq': 'Συχνές ερωτήσεις',
            'footer.contact': 'Επικοινωνία',
        },
    };

    function closeDropdowns(except) {
        document.querySelectorAll('.nav-item.open').forEach(item => {
            if (!except || item !== except) {
                item.classList.remove('open');
            }
        });
    }

    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', event => {
            const parent = trigger.closest('.nav-item');
            const isOpen = parent.classList.contains('open');
            closeDropdowns(isOpen ? null : parent);
            parent.classList.toggle('open', !isOpen);
            event.stopPropagation();
        });
        trigger.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                trigger.click();
            }
        });
    });

    document.addEventListener('click', event => {
        if (!event.target.closest('.nav-item')) {
            closeDropdowns();
        }
    });

    function openOverlay(card) {
        if (!overlay) {
            return;
        }
        const name = card.dataset.productName || '';
        const shortDesc = card.dataset.productShort || '';
        const longDesc = card.dataset.productLong || '';
        const price = card.dataset.productPrice || '0.00';
        const image = card.dataset.productImage || '';
        const productId = card.dataset.productId || '';

        overlayImage.src = image;
        overlayImage.alt = name;
        overlayTitle.textContent = name;
        overlayShort.textContent = shortDesc;
        overlayLong.textContent = longDesc;
        overlayPrice.textContent = `$${Number(price).toFixed(2)}`;
        if (overlayProductIdCart) {
            overlayProductIdCart.value = productId;
        }
        if (overlayProductIdSave) {
            overlayProductIdSave.value = productId;
        }
        if (overlayQuantity) {
            overlayQuantity.value = 1;
        }

        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
    }

    function closeOverlay() {
        if (!overlay) {
            return;
        }
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
    }

    if (overlayBackdrop) {
        overlayBackdrop.addEventListener('click', closeOverlay);
    }

    if (overlayClose) {
        overlayClose.addEventListener('click', closeOverlay);
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeOverlay();
        }
    });

    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', () => openOverlay(card));
        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openOverlay(card);
            }
        });
    });

    function applyLanguage(lang) {
        const language = translations[lang] ? lang : 'en';
        document.documentElement.lang = language === 'el' ? 'el' : 'en';
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            const text = translations[language][key];
            if (!text) {
                return;
            }
            const paramsRaw = element.getAttribute('data-i18n-params');
            let rendered = text;
            if (paramsRaw) {
                try {
                    const params = JSON.parse(paramsRaw);
                    Object.keys(params).forEach(paramKey => {
                        rendered = rendered.replace(`{${paramKey}}`, params[paramKey]);
                    });
                } catch (error) {
                    console.warn('Unable to parse translation params', error);
                }
            }
            element.innerHTML = rendered;
        });
        localStorage.setItem('crunchlabs-lang', language);
    }

    const storedLang = localStorage.getItem('crunchlabs-lang') || 'en';
    applyLanguage(storedLang);

    if (languageToggle) {
        languageToggle.addEventListener('click', () => {
            const current = localStorage.getItem('crunchlabs-lang') || 'en';
            const next = current === 'en' ? 'el' : 'en';
            applyLanguage(next);
        });
    }
})();
