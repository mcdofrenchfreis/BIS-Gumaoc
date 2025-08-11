# Form Background Images

This folder contains background images for official barangay forms and documents.

## Current Images

### barangay-letterhead-bg.png
- **Purpose**: Official letterhead background for Barangay Gumaoc East documents
- **Usage**: Used in business clearance forms and other official documents
- **Features**: 
  - Subtle geometric pattern background
  - Decorative wave-like bands in green, pink, and gold
  - Two circular seals (Barangay Gumaoc East and City of San Jose Del Monte)
  - Central watermark of the barangay seal

## How to Add New Background Images

1. **Image Requirements**:
   - Format: JPG or PNG
   - Resolution: High quality (at least 800x600px)
   - File size: Optimized for web (under 500KB recommended)

2. **Naming Convention**:
   - Use descriptive names: `[document-type]-bg.jpg`
   - Examples: `business-clearance-bg.jpg`, `certificate-bg.jpg`

3. **Usage in Forms**:
   ```css
   background-image: url('../assets/images/forms/[filename]');
   background-size: cover;
   background-position: center;
   background-repeat: no-repeat;
   opacity: 0.1; /* Adjust opacity as needed */
   ```

## Image Specifications

- **Background Pattern**: Subtle, professional geometric patterns
- **Color Scheme**: Green (#2e7d32), Pink (#ff69b4), Gold (#d2b48c)
- **Logos**: Official barangay and city seals
- **Watermark**: Faded official seal for authenticity
- **Borders**: Decorative wave-like bands at top and bottom

## Maintenance

- Keep original high-resolution files
- Optimize images for web use
- Test print quality before deployment
- Update this README when adding new images 