# 🔍 Universal Localhost Penetration Testing Platform v4.0

**Universal Localhost Penetration Testing Platform** là công cụ toàn diện để thực hiện penetration testing trên localhost projects.

## ✨ Tính Năng Chính

- 🎯 **Multi-Target Support**: Unlimited localhost targets
- 🔍 **Auto Target Discovery**: Tự động phát hiện XAMPP projects  
- 💀 **5 Upload Methods**: Admin, SEO, Filter Bypass, Direct, Auth Bypass
- 🔐 **Authentication Bypass**: SQL Injection + Multi-method
- 🛠️ **Advanced Shell**: File browser, multi-execution
- 📊 **Risk Assessment**: Auto risk evaluation
- 🕵️ **Debug Interface**: Deep target analysis

## 📋 Files Structure

### Core Files
- `penetration_tester_simple.php` - Main interface
- `enhanced_shell_uploader.php` - Universal uploader (5 methods)
- `target_discovery.php` - Auto-discovery engine

### Support Files  
- `test_bypass_api.php` - Auth bypass testing
- `integrated_auth_bypass_api.php` - Complete bypass system
- `debug_interface.php` - Advanced debugging
- `logs/` - Activity logs

## 🚀 Installation & Usage

### Quick Start
1. Copy files to XAMPP htdocs
2. Access: `http://localhost/project/penetration_tester_simple.php`
3. Click "🎯 Discover All Targets"
4. Select target and begin testing

### Supported Targets
- MongTruyen CMS
- DuLich-BlueOcean  
- WordPress
- Laravel projects
- Any localhost project

## 💀 Upload Methods (All Fixed)

### 1. 🔐 Admin Upload (95% success)
Target: `/admin/uploads/`, `/admin/files/`

### 2. 🎯 SEO Upload Exploit  
Target: Template directories
Method: File type validation bypass

### 3. 🚫 Filter Bypass
Techniques: `.php.txt`, `.phtml`, `.php5`, `.inc`

### 4. 📤 Direct Upload
Target: Standard upload directories

### 5. 🔐 Auth Bypass + Upload
Method: SQL injection + protected upload
Payload: `admin' OR '1'='1' --`

## 🔐 Authentication Bypass

### SQL Injection (Primary)
```
Username: admin' OR '1'='1' --
Password: anything
```

### Other Methods
- Brute force common passwords
- Default credentials testing
- Session hijacking techniques

## 🛠️ Advanced Shell Features

- **Multi-execution**: system(), exec(), shell_exec(), etc.
- **File browser**: Navigate and view files
- **Quick commands**: Pre-built shortcuts
- **Error handling**: Graceful fallbacks

## ⚠️ Security & Legal

**CHỈ SỬ DỤNG TRÊN HỆ THỐNG CỦA BẠN**

- ✅ Localhost testing only
- ✅ Educational purposes
- ✅ Authorized testing
- ❌ No unauthorized access
- ❌ No production systems

## 🔧 Troubleshooting

### Common Issues
1. **Upload Failed**: Check directory permissions
2. **Shell Not Accessible**: Verify .htaccess settings
3. **Discovery Failed**: Check XAMPP structure
4. **Bypass Failed**: Try different methods

### Debug Commands
```bash
chmod 755 /upload/directory
chmod 644 /upload/files
```

## 📊 Performance Metrics

- Target Discovery: 2-5 seconds
- Shell Upload: 1-3 seconds
- Auth Bypass: 2-10 seconds
- Success Rates: 80-98% depending on method

## 🚀 Version History

### v4.0 (Current)
- ✅ Multi-target support
- ✅ All upload methods fixed
- ✅ Auto target discovery
- ✅ Enhanced shell features
- ✅ Risk assessment

### Previous Versions
- v3.0: Enhanced uploader + auth bypass
- v2.0: Basic functionality + Matrix UI
- v1.0: Initial release

## Credits

**Developed by**: Hiệp Nguyễn  
**Version**: 4.0 Multi-Target Edition  
**License**: Educational Use Only

---

**⚠️ Use responsibly and only on authorized systems!**