# 📸 Frontend Image Upload Instructions - Cloud Storage Integration

## 🎯 Overview

The backend has been updated to accept **image URLs** instead of file uploads. The frontend must now handle image uploads to a cloud storage service (like Imgbb, Cloudinary, or similar) and send the resulting URLs to the backend API.

---

## 🔄 What Changed

### **BEFORE (Old System):**
- Frontend uploaded image files directly to backend
- Backend stored files in local storage (ephemeral on Railway)
- Images were lost on every deployment

### **AFTER (New System):**
- Frontend uploads images to cloud storage service (Imgbb, Cloudinary, etc.)
- Cloud service returns permanent URLs
- Frontend sends these URLs to backend
- Backend stores URLs in database
- Images persist permanently in cloud storage

---

## 🚀 Implementation Steps for Frontend

### **Step 1: Choose a Cloud Storage Service**

Recommended options:
1. **Imgbb** (Free, simple API, no signup required)
   - API: `https://api.imgbb.com/1/upload`
   - Get API key: https://api.imgbb.com/
   - Free tier: Unlimited uploads

2. **Cloudinary** (Free tier: 25GB storage)
   - More features, better for production
   - Get API key: https://cloudinary.com/

3. **Uploadcare** (Free tier: 3000 uploads/month)
   - Simple API
   - Get API key: https://uploadcare.com/

---

### **Step 2: Upload Images to Cloud Storage**

#### **Example Using Imgbb:**

```javascript
// Function to upload image to Imgbb
const uploadImageToImgbb = async (file) => {
  const IMGBB_API_KEY = 'your_imgbb_api_key_here'; // Get from https://api.imgbb.com/
  
  const formData = new FormData();
  formData.append('image', file);
  formData.append('key', IMGBB_API_KEY);
  
  try {
    const response = await fetch('https://api.imgbb.com/1/upload', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      return data.data.url; // Returns the permanent image URL
    } else {
      throw new Error('Image upload failed');
    }
  } catch (error) {
    console.error('Error uploading image:', error);
    throw error;
  }
};

// Upload multiple images
const uploadMultipleImages = async (files) => {
  const uploadPromises = files.map(file => uploadImageToImgbb(file));
  const imageUrls = await Promise.all(uploadPromises);
  return imageUrls; // Returns array of URLs
};
```

#### **Example Using Cloudinary:**

```javascript
// Function to upload image to Cloudinary
const uploadImageToCloudinary = async (file) => {
  const CLOUD_NAME = 'your_cloud_name';
  const UPLOAD_PRESET = 'your_upload_preset'; // Create unsigned preset in Cloudinary
  
  const formData = new FormData();
  formData.append('file', file);
  formData.append('upload_preset', UPLOAD_PRESET);
  
  try {
    const response = await fetch(
      `https://api.cloudinary.com/v1_1/${CLOUD_NAME}/image/upload`,
      {
        method: 'POST',
        body: formData
      }
    );
    
    const data = await response.json();
    return data.secure_url; // Returns the permanent HTTPS image URL
  } catch (error) {
    console.error('Error uploading image:', error);
    throw error;
  }
};
```

---

### **Step 3: Create Product/PreOrder with Image URLs**

#### **Creating a Product:**

```javascript
const createProduct = async (productData, imageFiles) => {
  try {
    // Step 1: Upload images to cloud storage
    const imageUrls = await uploadMultipleImages(imageFiles);
    
    // Step 2: Send product data with image URLs to backend
    const response = await fetch('https://web-production-d1120.up.railway.app/api/admin/products', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authToken}` // Your auth token
      },
      body: JSON.stringify({
        name: productData.name,
        category_id: productData.category_id,
        price: productData.price,
        stock: productData.stock,
        description: productData.description,
        power: productData.power,
        warranty: productData.warranty,
        specifications: productData.specifications,
        images: imageUrls, // ← Array of image URLs from cloud storage
        video_url: productData.video_url
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      console.log('Product created:', result.product);
      return result.product;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error('Error creating product:', error);
    throw error;
  }
};
```

#### **Creating a PreOrder:**

```javascript
const createPreOrder = async (preOrderData, imageFiles) => {
  try {
    // Step 1: Upload images to cloud storage
    const imageUrls = await uploadMultipleImages(imageFiles);
    
    // Step 2: Send pre-order data with image URLs to backend
    const response = await fetch('https://web-production-d1120.up.railway.app/api/admin/pre-orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authToken}` // Your auth token
      },
      body: JSON.stringify({
        product_name: preOrderData.product_name,
        category_id: preOrderData.category_id,
        pre_order_price: preOrderData.pre_order_price,
        deposit_percentage: preOrderData.deposit_percentage,
        expected_availability: preOrderData.expected_availability,
        power_output: preOrderData.power_output,
        warranty_period: preOrderData.warranty_period,
        specifications: preOrderData.specifications,
        images: imageUrls, // ← Array of image URLs from cloud storage
        video_url: preOrderData.video_url
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      console.log('Pre-order created:', result.data);
      return result.data;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error('Error creating pre-order:', error);
    throw error;
  }
};
```

---

### **Step 4: Update Product/PreOrder with New Images**

```javascript
const updateProduct = async (productId, productData, imageFiles) => {
  try {
    let imageUrls = productData.existingImages || []; // Keep existing images
    
    // If new images are provided, upload them
    if (imageFiles && imageFiles.length > 0) {
      const newImageUrls = await uploadMultipleImages(imageFiles);
      imageUrls = [...imageUrls, ...newImageUrls]; // Combine existing + new
    }
    
    const response = await fetch(`https://web-production-d1120.up.railway.app/api/admin/products/${productId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authToken}`
      },
      body: JSON.stringify({
        ...productData,
        images: imageUrls // All image URLs (existing + new)
      })
    });
    
    const result = await response.json();
    return result.product;
  } catch (error) {
    console.error('Error updating product:', error);
    throw error;
  }
};
```

---

### **Step 5: Display Images in Frontend**

#### **Display Product Images:**

```javascript
// In your product display component
const ProductDisplay = ({ product }) => {
  return (
    <div className="product">
      <h2>{product.name}</h2>
      <p>{product.description}</p>
      
      {/* Display images - they're already full URLs */}
      <div className="product-images">
        {product.images && product.images.map((imageUrl, index) => (
          <img 
            key={index}
            src={imageUrl} 
            alt={`${product.name} - Image ${index + 1}`}
            className="product-image"
          />
        ))}
      </div>
      
      <p className="price">${product.price}</p>
      <p className="stock">Stock: {product.stock}</p>
    </div>
  );
};
```

#### **React Example with Form:**

```jsx
import { useState } from 'react';

const ProductForm = () => {
  const [productData, setProductData] = useState({
    name: '',
    category_id: '',
    price: '',
    stock: '',
    description: '',
    power: '',
    warranty: '',
    specifications: [],
    video_url: ''
  });
  
  const [imageFiles, setImageFiles] = useState([]);
  const [uploading, setUploading] = useState(false);
  
  const handleImageChange = (e) => {
    const files = Array.from(e.target.files);
    setImageFiles(files);
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setUploading(true);
    
    try {
      // Upload images first
      const imageUrls = await uploadMultipleImages(imageFiles);
      
      // Create product with image URLs
      const response = await fetch('https://web-production-d1120.up.railway.app/api/admin/products', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          ...productData,
          images: imageUrls
        })
      });
      
      const result = await response.json();
      
      if (result.success) {
        alert('Product created successfully!');
        // Reset form or redirect
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Failed to create product');
    } finally {
      setUploading(false);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        placeholder="Product Name"
        value={productData.name}
        onChange={(e) => setProductData({...productData, name: e.target.value})}
        required
      />
      
      {/* Other input fields... */}
      
      <input
        type="file"
        multiple
        accept="image/*"
        onChange={handleImageChange}
      />
      
      <button type="submit" disabled={uploading}>
        {uploading ? 'Uploading...' : 'Create Product'}
      </button>
    </form>
  );
};
```

---

## 📋 Backend API Expectations

### **Product Endpoints:**

#### `POST /api/admin/products`
```json
{
  "name": "Solar Panel 500W",
  "category_id": 1,
  "price": 45000,
  "stock": 10,
  "description": "High efficiency solar panel",
  "power": "500W",
  "warranty": "25 years",
  "specifications": ["Specification 1", "Specification 2"],
  "images": [
    "https://i.ibb.co/xyz123/image1.jpg",
    "https://i.ibb.co/abc456/image2.jpg",
    "https://i.ibb.co/def789/image3.jpg"
  ],
  "video_url": "https://youtube.com/watch?v=..."
}
```

#### `PUT /api/admin/products/{id}`
```json
{
  "name": "Updated Product Name",
  "category_id": 1,
  "price": 50000,
  "stock": 15,
  "description": "Updated description",
  "power": "600W",
  "warranty": "30 years",
  "specifications": ["New spec 1", "New spec 2"],
  "images": [
    "https://i.ibb.co/xyz123/image1.jpg", // existing or new URLs
    "https://i.ibb.co/newimg/image4.jpg"  // newly uploaded
  ],
  "video_url": "https://youtube.com/watch?v=..."
}
```

### **PreOrder Endpoints:**

#### `POST /api/admin/pre-orders`
```json
{
  "product_name": "Solar Inverter 5KW",
  "category_id": 2,
  "pre_order_price": 80000,
  "deposit_percentage": 30,
  "expected_availability": "March 2025",
  "power_output": "5KW",
  "warranty_period": "10 years",
  "specifications": "High efficiency inverter",
  "images": [
    "https://i.ibb.co/xyz123/inverter1.jpg",
    "https://i.ibb.co/abc456/inverter2.jpg"
  ],
  "video_url": "https://youtube.com/watch?v=..."
}
```

#### `PUT /api/admin/pre-orders/{id}`
```json
{
  "product_name": "Updated Inverter Name",
  "category_id": 2,
  "pre_order_price": 85000,
  "deposit_percentage": 35,
  "expected_availability": "April 2025",
  "power_output": "5.5KW",
  "warranty_period": "12 years",
  "specifications": "Updated specs",
  "images": [
    "https://i.ibb.co/xyz123/inverter1.jpg",
    "https://i.ibb.co/newimg/inverter3.jpg"
  ],
  "video_url": "https://youtube.com/watch?v=..."
}
```

---

## 📊 API Response Format

### **Product Response:**
```json
{
  "success": true,
  "message": "Product created successfully",
  "product": {
    "id": 1,
    "name": "Solar Panel 500W",
    "description": "High efficiency solar panel",
    "price": "45000.00",
    "formatted_price": "₦45,000.00",
    "stock": 10,
    "in_stock": true,
    "stock_status": "In Stock",
    "power": "500W",
    "warranty": "25 years",
    "specifications": ["Spec 1", "Spec 2"],
    "images": [
      "https://i.ibb.co/xyz123/image1.jpg",
      "https://i.ibb.co/abc456/image2.jpg"
    ],
    "video_url": "https://youtube.com/watch?v=...",
    "category": {
      "id": 1,
      "name": "Solar Panels",
      "slug": "solar-panels"
    },
    "created_at": "2025-11-12 14:30:00",
    "updated_at": "2025-11-12 14:30:00"
  }
}
```

### **PreOrder Response:**
```json
{
  "success": true,
  "message": "Pre-order created successfully",
  "data": {
    "id": 1,
    "product_name": "Solar Inverter 5KW",
    "pre_order_price": "80000.00",
    "formatted_price": "₦80,000.00",
    "deposit_percentage": "30.00",
    "deposit_amount": 24000.00,
    "formatted_deposit": "₦24,000.00",
    "expected_availability": "March 2025",
    "power_output": "5KW",
    "warranty_period": "10 years",
    "specifications": "High efficiency inverter",
    "images": [
      "https://i.ibb.co/xyz123/inverter1.jpg",
      "https://i.ibb.co/abc456/inverter2.jpg"
    ],
    "video_url": "https://youtube.com/watch?v=...",
    "category": {
      "id": 2,
      "name": "Inverters",
      "slug": "inverters"
    },
    "created_at": "2025-11-12 14:35:00",
    "updated_at": "2025-11-12 14:35:00"
  }
}
```

---

## ⚠️ Important Notes

1. **No More File Uploads:** The backend NO LONGER accepts `multipart/form-data` with file uploads. It now expects JSON with image URLs.

2. **Content-Type:** Always use `Content-Type: application/json` when sending requests to the backend.

3. **Image Array:** The `images` field must be an array of strings (URLs), not files.

4. **Validation:** Each image URL will be validated as a proper URL format.

5. **Maximum Images:** Both products and pre-orders support up to 10 images maximum.

6. **Persistent Storage:** Images are now permanently stored in cloud storage and will NOT be lost on deployments.

7. **Image Order:** The order of URLs in the array is preserved and determines display order.

---

## 🎨 UI/UX Recommendations

1. **Show Upload Progress:** Display a loading indicator while images are being uploaded to cloud storage.

2. **Preview Images:** Show thumbnail previews of selected images before upload.

3. **Drag & Drop:** Implement drag-and-drop for better UX.

4. **Remove Images:** Allow users to remove individual images before submitting.

5. **Error Handling:** Show clear error messages if cloud upload fails.

6. **Image Optimization:** Consider resizing/compressing images before upload to cloud storage.

---

## 📝 Summary for Frontend Developer

**What You Need to Do:**

1. ✅ Get an API key from Imgbb (or Cloudinary/Uploadcare)
2. ✅ Implement image upload function that uploads to cloud service
3. ✅ Get image URLs from cloud service response
4. ✅ Send image URLs (not files) to backend API
5. ✅ Display images using the URLs returned from backend
6. ✅ Use `Content-Type: application/json` for all requests
7. ✅ Handle loading states during image upload

**What the Backend Does:**

- ✅ Accepts array of image URLs (not files)
- ✅ Stores URLs in database
- ✅ Returns same URLs in API responses
- ✅ No local file storage, no ephemeral filesystem issues

---

## 🔗 Helpful Links

- Imgbb API Docs: https://api.imgbb.com/
- Cloudinary Docs: https://cloudinary.com/documentation
- Uploadcare Docs: https://uploadcare.com/docs/

---

**Questions?** The backend is ready to accept image URLs. Just implement the cloud upload on your frontend and you're good to go! 🚀
