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
            'hero.title': 'Forge your future in robotics',
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
            'catalog.compareTitle': 'Compare every RoboForge prototype',
            'catalog.compareSubtitle': 'Dive into each learning stage, open the overlay for specs, and add the kits you need directly to your cart.',
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
            'checkout.country': 'Country (ISO code preferred)',
            'checkout.email': 'Email',
            'checkout.phone': 'Phone',
            'checkout.paymentTitle': 'Secure payment',
            'checkout.secureNote': 'Card details are encrypted and handled directly by Adyen to keep RoboForge out of PCI scope.',
            'checkout.processing': 'Processing your payment…',
            'checkout.success': 'Payment authorised! We will email your receipt shortly.',
            'checkout.pending': 'Action required — please follow the 3D Secure prompt.',
            'checkout.errorConfig': 'Payment is temporarily unavailable. Please contact support.',
            'checkout.errorValidation': 'Please complete all required shipping fields before paying.',
            'checkout.errorPayment': 'We could not process the payment. Please try another method.',
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
            'hero.title': 'Σφυρηλατήστε το μέλλον σας στη ρομποτική',
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
            'catalog.compareTitle': 'Συγκρίνετε όλα τα πρωτότυπα RoboForge',
            'catalog.compareSubtitle': 'Εξερευνήστε κάθε εκπαιδευτικό επίπεδο, δείτε τις λεπτομέρειες και προσθέστε άμεσα τα κιτ που χρειάζεστε στο καλάθι σας.',
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
            'checkout.country': 'Χώρα (προτιμάται ο κωδικός ISO)',
            'checkout.email': 'Ηλεκτρονικό ταχυδρομείο',
            'checkout.phone': 'Τηλέφωνο',
            'checkout.paymentTitle': 'Ασφαλής πληρωμή',
            'checkout.secureNote': 'Τα στοιχεία κάρτας κρυπτογραφούνται και διαχειρίζονται απευθείας από την Adyen ώστε το RoboForge να παραμένει εκτός PCI scope.',
            'checkout.processing': 'Επεξεργασία πληρωμής…',
            'checkout.success': 'Η πληρωμή εγκρίθηκε! Θα στείλουμε την απόδειξη σύντομα.',
            'checkout.pending': 'Απαιτείται ενέργεια — ακολουθήστε την προτροπή 3D Secure.',
            'checkout.errorConfig': 'Η πληρωμή δεν είναι διαθέσιμη προσωρινά. Επικοινωνήστε με την υποστήριξη.',
            'checkout.errorValidation': 'Συμπληρώστε όλα τα υποχρεωτικά στοιχεία αποστολής πριν την πληρωμή.',
            'checkout.errorPayment': 'Δεν μπορέσαμε να ολοκληρώσουμε την πληρωμή. Δοκιμάστε άλλη μέθοδο.',
            'footer.about': 'Σχετικά',
            'footer.faq': 'Συχνές ερωτήσεις',
            'footer.contact': 'Επικοινωνία',
        },
    };

    function closeDropdowns(except) {
        document.querySelectorAll('.nav-item.open').forEach(item => {
            if (!except || item !== except) {
                item.classList.remove('open');
                const trigger = item.querySelector('.nav-trigger');
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', event => {
            const parent = trigger.closest('.nav-item');
            const isOpen = parent.classList.contains('open');
            closeDropdowns(isOpen ? null : parent);
            parent.classList.toggle('open', !isOpen);
            trigger.setAttribute('aria-expanded', String(!isOpen));
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

    let activeLanguage = localStorage.getItem('roboforge-lang') || 'en';

    function applyLanguage(lang) {
        const language = translations[lang] ? lang : 'en';
        activeLanguage = language;
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
            if (element.hasAttribute('data-i18n-html')) {
                element.innerHTML = rendered;
            } else {
                element.textContent = rendered;
            }
        });
        localStorage.setItem('roboforge-lang', language);
    }

    applyLanguage(activeLanguage);

    if (languageToggle) {
        languageToggle.addEventListener('click', () => {
            const next = activeLanguage === 'en' ? 'el' : 'en';
            applyLanguage(next);
        });
    }

    function translate(key) {
        const bundle = translations[activeLanguage] || {};
        if (bundle[key]) {
            return bundle[key];
        }
        return translations.en[key] || key;
    }

    const checkoutForm = document.getElementById('checkout-form');
    const dropinContainer = document.getElementById('adyen-dropin');
    const paymentMessages = document.getElementById('payment-messages');

    if (checkoutForm && dropinContainer) {
        checkoutForm.addEventListener('submit', event => event.preventDefault());

        const paymentConfig = window.roboforgePaymentConfig || null;
        let currentPaymentId = null;

        function setPaymentMessage(type, message) {
            if (!paymentMessages) {
                return;
            }
            paymentMessages.textContent = message;
            paymentMessages.className = 'payment-message payment-message--' + type;
        }

        function collectShippingData() {
            const data = {};
            let valid = true;
            checkoutForm.querySelectorAll('[data-checkout-field]').forEach(input => {
                const name = input.getAttribute('name');
                if (!name) {
                    return;
                }
                const value = input.value.trim();
                data[name] = value;
                if (input.hasAttribute('required') && value === '') {
                    valid = false;
                }
            });
            return { data, valid };
        }

        function waitForAdyen() {
            return new Promise((resolve, reject) => {
                if (window.AdyenCheckout) {
                    resolve(window.AdyenCheckout);
                    return;
                }
                const timeout = setTimeout(() => reject(new Error('Adyen SDK failed to load')), 10000);
                const check = () => {
                    if (window.AdyenCheckout) {
                        clearTimeout(timeout);
                        resolve(window.AdyenCheckout);
                    } else {
                        requestAnimationFrame(check);
                    }
                };
                check();
            });
        }

        function handleFinalResult(result, component) {
            const code = result.resultCode || '';
            if (code === 'Authorised') {
                setPaymentMessage('success', translate('checkout.success'));
                component.setStatus('success');
                setTimeout(() => window.location.reload(), 1500);
                return;
            }
            if (code === 'Pending' || code === 'Received') {
                setPaymentMessage('info', translate('checkout.pending'));
                component.setStatus('ready');
                return;
            }
            const message = result.error || translate('checkout.errorPayment');
            setPaymentMessage('error', message);
            component.setStatus('ready');
        }

        async function handleAdditionalDetails(state, component) {
            if (!currentPaymentId) {
                component.setStatus('ready');
                return;
            }
            try {
                const response = await fetch(paymentConfig.detailsUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        paymentId: currentPaymentId,
                        details: state.data,
                    }),
                });
                const payload = await response.json();
                if (!response.ok || payload.error) {
                    throw new Error(payload.error || translate('checkout.errorPayment'));
                }
                handleFinalResult(payload, component);
            } catch (error) {
                console.error('Adyen additional details error', error);
                setPaymentMessage('error', error.message || translate('checkout.errorPayment'));
                component.setStatus('ready');
            }
        }

        async function handlePayment(state, component, configData) {
            const shipping = collectShippingData();
            if (!shipping.valid) {
                setPaymentMessage('error', translate('checkout.errorValidation'));
                component.setStatus('ready');
                return;
            }
            component.setStatus('loading');
            setPaymentMessage('info', translate('checkout.processing'));
            try {
                const response = await fetch(paymentConfig.createPaymentUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        shipping: shipping.data,
                        paymentMethod: state.data.paymentMethod,
                        browserInfo: state.data.browserInfo,
                        billingAddress: state.data.billingAddress || null,
                        storePaymentMethod: state.data.storePaymentMethod || false,
                        amount: configData.amount,
                    }),
                });
                const payload = await response.json();
                if (!response.ok || payload.error) {
                    throw new Error(payload.error || translate('checkout.errorPayment'));
                }
                currentPaymentId = payload.paymentId || null;
                if (payload.action) {
                    setPaymentMessage('info', translate('checkout.pending'));
                    component.handleAction(payload.action);
                    return;
                }
                handleFinalResult(payload, component);
            } catch (error) {
                console.error('Adyen payment error', error);
                setPaymentMessage('error', error.message || translate('checkout.errorPayment'));
                component.setStatus('ready');
            }
        }

        async function initialiseCheckout() {
            if (!paymentConfig || !paymentConfig.configUrl) {
                setPaymentMessage('error', translate('checkout.errorConfig'));
                return;
            }
            try {
                const response = await fetch(paymentConfig.configUrl, { credentials: 'same-origin' });
                const payload = await response.json();
                if (!response.ok || payload.error) {
                    throw new Error(payload.error || translate('checkout.errorConfig'));
                }
                await waitForAdyen();
                const checkout = await AdyenCheckout({
                    environment: payload.environment,
                    clientKey: payload.clientKey,
                    analytics: { enabled: false },
                    locale: payload.locale || (activeLanguage === 'el' ? 'el-GR' : 'en-US'),
                    paymentMethodsResponse: payload.paymentMethodsResponse,
                    amount: payload.amount,
                    onSubmit: (state, component) => handlePayment(state, component, payload),
                    onAdditionalDetails: handleAdditionalDetails,
                    onError: error => {
                        console.error('Adyen drop-in error', error);
                        setPaymentMessage('error', translate('checkout.errorPayment'));
                    },
                    paymentMethodsConfiguration: {
                        card: {
                            hasHolderName: true,
                            holderNameRequired: true,
                            showPayButton: true,
                            enableStoreDetails: true,
                            billingAddressRequired: true,
                        },
                    },
                });
                checkout.create('dropin').mount(dropinContainer);
            } catch (error) {
                console.error('Adyen initialisation error', error);
                setPaymentMessage('error', error.message || translate('checkout.errorConfig'));
            }
        }

        initialiseCheckout();
    }
})();
