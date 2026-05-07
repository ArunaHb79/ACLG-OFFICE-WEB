# Chatbot AI Integration Setup Guide

## 📋 Overview
Your chatbot now supports both **rule-based** and **AI-powered** responses with multilingual support (English, Sinhala, Tamil).

## 🎯 Current Features

### Rule-Based Responses (Active Now)
The chatbot currently uses enhanced pattern matching with responses for:

- ✅ **Greetings** - Hello, hi, good morning (all 3 languages)
- ✅ **Departments** - All 5 department information
- ✅ **Contact Info** - Phone, email, address
- ✅ **Office Hours** - Working schedule
- ✅ **Downloads** - Forms and documents
- ✅ **Job Inquiries** - Recruitment information
- ✅ **Applications** - How to apply for services
- ✅ **Payments** - Financial queries
- ✅ **Complaints** - How to file complaints
- ✅ **Projects** - Development initiatives
- ✅ **Services** - What the office provides
- ✅ **Location** - Address and directions
- ✅ **Help** - General assistance

## 🤖 AI Integration Options

### Option 1: OpenAI (ChatGPT)

**Steps to Enable:**

1. **Get API Key:**
   - Visit: https://platform.openai.com/api-keys
   - Create account or login
   - Generate new API key
   - Copy the key

2. **Configure in script.js:**
   ```javascript
   const AI_CONFIG = {
       enabled: true,
       provider: 'openai',
       apiKey: 'sk-your-api-key-here', // Paste your key
       model: 'gpt-3.5-turbo', // or 'gpt-4' for better quality
       maxTokens: 150,
       temperature: 0.7
   };
   ```

3. **Cost:**
   - GPT-3.5-turbo: ~$0.002 per 1K tokens
   - GPT-4: ~$0.03 per 1K tokens
   - Estimate: ~100-200 conversations per $1

---

### Option 2: Google Gemini (Free Option)

**Steps to Enable:**

1. **Get API Key:**
   - Visit: https://makersuite.google.com/app/apikey
   - Sign in with Google account
   - Create API key
   - Copy the key

2. **Configure in script.js:**
   ```javascript
   const AI_CONFIG = {
       enabled: true,
       provider: 'gemini',
       apiKey: 'your-gemini-api-key-here', // Paste your key
       model: 'gemini-pro',
       maxTokens: 150,
       temperature: 0.7
   };
   ```

3. **Cost:**
   - FREE for up to 60 requests per minute
   - Good for moderate traffic websites

---

### Option 3: Keep Rule-Based (No Cost)

If you prefer not to use AI services, the chatbot works perfectly with rule-based responses.

**Advantages:**
- ✅ No API costs
- ✅ Instant responses
- ✅ Complete privacy
- ✅ Works offline
- ✅ Fully customizable

**To keep rule-based only:**
```javascript
const AI_CONFIG = {
    enabled: false, // Keep this false
    // ... rest doesn't matter
};
```

---

## 🔧 How It Works

### Hybrid System (When AI is enabled):

1. User sends message
2. System tries AI first
3. If AI fails/disabled → Falls back to rule-based
4. Response displayed to user

### Response Priority:
```
User Message → AI Service (if enabled) → Rule-Based Patterns → Response
```

---

## 📝 Adding More Patterns (Rule-Based)

To add new patterns, edit the `getBotResponse()` function in **script.js**:

```javascript
// Example: Adding weather queries
const weather = ['weather', 'rain', 'temperature', 'කාලගුණය', 'மழை'];
if (weather.some(w => input.includes(w))) {
    return "For weather updates, please check the Department of Meteorology website.";
}
```

---

## 🌍 Multilingual Support

The system automatically:
- Detects current page language
- Responds in that language
- Recognizes keywords in all 3 languages
- AI responds in selected language

**Languages Supported:**
- 🇬🇧 English
- 🇱🇰 Sinhala (සිංහල)
- 🇱🇰 Tamil (தமிழ்)

---

## 🔒 Security Best Practices

### For AI Integration:

1. **Never commit API keys to Git:**
   ```bash
   # Add to .gitignore
   script.js  # If it contains keys
   ```

2. **Better approach - Use environment variables:**
   - Store keys in server-side config
   - Create API endpoint on your server
   - Chatbot calls your server, not AI directly

3. **Rate limiting:**
   - Implement limits to prevent abuse
   - Monitor API usage
   - Set spending limits in AI dashboard

---

## 📊 Monitoring & Analytics

### Track Chatbot Usage:

Add analytics to see:
- Most asked questions
- Response satisfaction
- API usage/costs
- Popular times

```javascript
// Example: Log questions
function sendMessage() {
    // ... existing code ...
    console.log('User asked:', message);
    // Send to your analytics service
}
```

---

## 🚀 Deployment Recommendations

### For Production:

1. **Create Backend API:**
   ```
   Your Website → Your Server → AI Service
   ```
   
2. **Benefits:**
   - Hide API keys
   - Add caching
   - Implement rate limiting
   - Monitor usage
   - Better security

3. **Example Backend (Node.js):**
   ```javascript
   // server.js
   app.post('/api/chat', async (req, res) => {
       const { message } = req.body;
       // Call AI API here
       // Return response
   });
   ```

---

## 🆘 Troubleshooting

### AI not responding:
- ✅ Check API key is correct
- ✅ Check `enabled: true` in config
- ✅ Check browser console for errors
- ✅ Verify API quota not exceeded

### Rule-based not working:
- ✅ Check keyword spelling
- ✅ Verify translations.js loaded
- ✅ Check browser console for errors

### Language switching issues:
- ✅ Refresh page after language change
- ✅ Clear browser cache

---

## 💡 Tips for Better Responses

### Rule-Based:
- Add more keyword variations
- Include common misspellings
- Add context-specific responses
- Test with real user questions

### AI-Powered:
- Adjust `temperature` (0.7 = balanced, 0.3 = focused, 1.0 = creative)
- Increase `maxTokens` for longer responses
- Update system context for better accuracy
- Monitor and improve based on user feedback

---

## 📞 Support

For technical assistance:
- Check script.js comments
- Review browser console errors
- Test with simple questions first

---

## 🔄 Future Enhancements

Possible additions:
- [ ] Voice input/output
- [ ] File upload support
- [ ] Image sharing
- [ ] Integration with backend database
- [ ] Admin dashboard
- [ ] Analytics tracking
- [ ] Feedback collection
- [ ] Multi-turn conversations
- [ ] Suggested questions based on context

---

## ✅ Current Status

**Active Features:**
- ✅ Rule-based chatbot (working)
- ✅ Multilingual support (EN/SI/TA)
- ✅ Quick action buttons
- ✅ Conversation history
- ✅ Typing indicators
- ✅ Mobile responsive
- ⏳ AI integration (ready, needs API key)

**To Enable AI:** Simply add your API key to the `AI_CONFIG` in script.js and set `enabled: true`.

---

© 2026 ACLG Hambantota - Chatbot System
