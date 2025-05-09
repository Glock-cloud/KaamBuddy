// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Rating system for reviews
    const ratingInputs = document.querySelectorAll('.rating-input i');
    if (ratingInputs.length > 0) {
        ratingInputs.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('data-value'));
                const hiddenInput = document.querySelector('input[name="rating"]');
                
                // Update hidden input value
                hiddenInput.value = value;
                
                // Update visual stars
                ratingInputs.forEach(s => {
                    if (parseInt(s.getAttribute('data-value')) <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
            
            // Hover effects
            star.addEventListener('mouseover', function() {
                const value = parseInt(this.getAttribute('data-value'));
                
                ratingInputs.forEach(s => {
                    if (parseInt(s.getAttribute('data-value')) <= value) {
                        s.classList.add('hover');
                    } else {
                        s.classList.remove('hover');
                    }
                });
            });
            
            star.addEventListener('mouseout', function() {
                ratingInputs.forEach(s => {
                    s.classList.remove('hover');
                });
            });
        });
    }
    
    // Image upload preview for registration form
    const profileImageInput = document.getElementById('profile-image');
    const profileImagePreview = document.getElementById('profile-image-preview');
    
    if (profileImageInput && profileImagePreview) {
        profileImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    profileImagePreview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview">`;
                    profileImagePreview.style.display = 'block';
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Work images upload preview
    const workImagesInput = document.getElementById('work-images');
    const workImagesPreview = document.getElementById('work-images-preview');
    
    if (workImagesInput && workImagesPreview) {
        workImagesInput.addEventListener('change', function() {
            workImagesPreview.innerHTML = '';
            
            if (this.files.length > 0) {
                const fragment = document.createDocumentFragment();
                
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `<img src="${e.target.result}" alt="Work Preview ${i+1}">`;
                        fragment.appendChild(previewItem);
                        
                        // If this is the last file, append the fragment
                        if (i === workImagesInput.files.length - 1) {
                            workImagesPreview.appendChild(fragment);
                            workImagesPreview.style.display = 'flex';
                        }
                    };
                    
                    reader.readAsDataURL(file);
                }
            } else {
                workImagesPreview.style.display = 'none';
            }
        });
    }
    
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('nav ul');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }
    
    // Review image upload preview
    const reviewImageInput = document.getElementById('review-image');
    const reviewImagePreview = document.getElementById('review-image-preview');
    
    if (reviewImageInput && reviewImagePreview) {
        reviewImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    reviewImagePreview.innerHTML = `<img src="${e.target.result}" alt="Review Image Preview">`;
                    reviewImagePreview.style.display = 'block';
                };
                
                reader.readAsDataURL(file);
            } else {
                reviewImagePreview.style.display = 'none';
            }
        });
    }
    
    // Image lightbox/gallery functionality
    const galleryItems = document.querySelectorAll('.gallery-item img');
    
    if (galleryItems.length > 0) {
        // Create lightbox elements
        const lightbox = document.createElement('div');
        lightbox.id = 'lightbox';
        lightbox.className = 'lightbox';
        
        const lightboxContent = document.createElement('div');
        lightboxContent.className = 'lightbox-content';
        
        const lightboxImage = document.createElement('img');
        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-btn';
        closeBtn.innerHTML = '&times;';
        
        lightboxContent.appendChild(lightboxImage);
        lightboxContent.appendChild(closeBtn);
        lightbox.appendChild(lightboxContent);
        document.body.appendChild(lightbox);
        
        // Add click event to gallery images
        galleryItems.forEach(img => {
            img.addEventListener('click', function() {
                lightboxImage.src = this.src;
                lightbox.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
        
        // Close lightbox
        closeBtn.addEventListener('click', function() {
            lightbox.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        lightbox.addEventListener('click', function(e) {
            if (e.target === this) {
                lightbox.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }
    
    // Form validation
    const registrationForm = document.getElementById('registration-form');
    
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get form fields
            const name = document.getElementById('name');
            const phone = document.getElementById('phone');
            const whatsapp = document.getElementById('whatsapp');
            const services = document.getElementById('services');
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            
            // Validate name
            if (!name.value.trim()) {
                displayError(name, 'Name is required');
                isValid = false;
            }
            
            // Validate phone
            if (!phone.value.trim()) {
                displayError(phone, 'Phone number is required');
                isValid = false;
            } else if (!isValidPhone(phone.value)) {
                displayError(phone, 'Please enter a valid phone number');
                isValid = false;
            }
            
            // Validate WhatsApp
            if (!whatsapp.value.trim()) {
                displayError(whatsapp, 'WhatsApp number is required');
                isValid = false;
            } else if (!isValidPhone(whatsapp.value)) {
                displayError(whatsapp, 'Please enter a valid WhatsApp number');
                isValid = false;
            }
            
            // Validate services
            if (!services.value.trim()) {
                displayError(services, 'Please describe your services');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Helper function to display error
    function displayError(inputElement, message) {
        const error = document.createElement('div');
        error.className = 'error-message';
        error.textContent = message;
        error.style.color = 'red';
        error.style.fontSize = '14px';
        error.style.marginTop = '5px';
        
        inputElement.parentNode.appendChild(error);
        inputElement.style.borderColor = 'red';
    }
    
    // Helper function to validate phone number
    function isValidPhone(phone) {
        // Allow digits, +, -, spaces and parentheses
        const phoneRegex = /^[0-9+\- ()]+$/;
        return phoneRegex.test(phone) && phone.replace(/[^0-9]/g, '').length >= 10;
    }
}); 