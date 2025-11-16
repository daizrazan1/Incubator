# PC Part Sniper

## Overview

PC Part Sniper is a web-based PC building and part comparison platform that helps users search, compare, and assemble computer parts. The platform features a comprehensive compatibility checker to simplify the PC building process. It features affiliate pricing, build management, and a modern, fluid dark-themed user interface inspired by PCPartPicker but with enhanced UX through smoother transitions and cleaner design.

**Status**: MVP Complete and Functional ✓

**Last Updated**: November 16, 2025 - Migrated to MySQL database and implemented user authentication system

The MVP includes:
- Full-featured part search with category, brand, and price filters
- Custom build creation and management with real-time compatibility checking
- Advanced compatibility validation (CPU/Motherboard sockets, RAM DDR types, PSU wattage, form factors)
- Multi-merchant pricing with affiliate link tracking
- **User registration and login system with secure authentication**
- User profile with saved builds and reviews
- Featured/trending builds showcase
- Responsive dark-themed UI with gradient accents

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture

**Technology Stack:**
- Vanilla JavaScript (ES6+) for client-side interactions
- Modern CSS with CSS custom properties (variables) for theming
- PHP-based server-side rendering with `.php` page extensions

**Design System:**
- Dark theme color palette with accent colors (primary: #1E1E1E, accent: #0088FF, highlight: #3BFF7F)
- Responsive layout with centered container (max-width: 1200px)
- Sticky navigation with shadowing for depth perception
- Component-based CSS architecture using BEM-style naming

**Key Frontend Features:**
- Form-based search with query parameter routing (`parts.php?query=...`)
- Auto-submit filter forms for dynamic content updates
- LocalStorage-based build state management for guest users
- AJAX-based part addition to builds without page reload

**Routing Pattern:**
- Server-side routing through PHP files (index.php, parts.php, build.php)
- Query string parameters for state management and filtering
- Hash-based or query-based build identification (`build.php?build_id=...`)

### Backend Architecture

**Server-Side Framework:**
- PHP for server-side logic and templating
- Controller-based architecture (e.g., `search_controller.php`)
- Separation of concerns between presentation (.php pages) and business logic (controllers)

**Data Access Pattern:**
- Direct SQL queries through controllers
- READ operations: Featured builds, trending parts, part searches
- WRITE operations: Click tracking, build management

**Key Backend Components:**
- Search controller for part querying and filtering
- Build management API endpoints (`/api/add_to_build.php`)
- Click tracking system for affiliate monetization
- Featured builds query system with JOIN operations

**API Structure:**
- RESTful JSON API endpoints under `/api/` directory
- POST-based mutations (add to build)
- JSON request/response format for AJAX operations
- Success/error response structure with message fields

### Data Architecture

**Core Database Tables:**
- `parts` - Component catalog with specifications and pricing
- `builds` - User-created PC configurations
- `build_parts` - Junction table linking builds to parts (many-to-many)
- `click_tracking` - Affiliate link click events (user_id, part_id, merchant_id)

**Data Relationships:**
- Builds contain multiple parts through `build_parts` junction table
- Parts can appear in multiple builds
- Click tracking links users, parts, and merchants for analytics

**State Management:**
- Server-side: Session-based user authentication (implied)
- Client-side: LocalStorage for guest build persistence
- Hybrid approach: LocalStorage buildId syncs with server on operations

### Authentication & Authorization

**Session Management:**
- PHP session-based authentication with secure session handling
- Session regeneration on login to prevent session fixation attacks
- Session destruction on logout for security
- Demo user available (username: demo_user, password: demo123)

**Authentication Pages:**
- `register.php` - User registration with validation (username, email, password)
- `login.php` - User login with bcrypt password verification and redirect protection
- `logout.php` - Session cleanup and logout

**Authorization Model:**
- Public read access to parts catalog and featured builds
- User-scoped build management (builds tied to user_id or session)
- Navigation displays Login/Register for guests, username and Logout for authenticated users
- No explicit role-based access control in MVP

**Security Features:**
- Bcrypt password hashing (PASSWORD_DEFAULT)
- Prepared statements for SQL injection prevention
- Open redirect protection on login redirects
- Session fixation protection via session_regenerate_id()

## External Dependencies

### Third-Party Services

**Affiliate Networks:**
- Multiple merchant integrations for part pricing and purchase links
- Click tracking system logs merchant_id for attribution
- External redirect flow for affiliate monetization

**Potential AI Integration:**
- AI recommendation system mentioned in PRD (implementation details not in codebase)
- Likely API-based integration for component compatibility and suggestions

### Database System

**Database Technology:**
- **External MySQL Database** (Hostinger-hosted)
- Host: srv941.hstgr.io
- Database: u237055794_comp
- Connection via MySQLi with UTF-8 (utf8mb4) character set
- Standard RDBMS features: JOINs, transactions, foreign keys, indexes

**Database Tables:**
1. `users` - User accounts with authentication
2. `merchants` - Retailer/merchant information
3. `parts` - PC component catalog
4. `part_prices` - Multi-merchant pricing
5. `price_history` - Historical price tracking
6. `builds` - User PC builds
7. `build_parts` - Build-to-parts junction table
8. `reviews` - User reviews for parts
9. `click_tracking` - Affiliate link analytics
10. `support_tickets` - Customer support system

**Database Files:**
- `db_config.php` - Database connection and helper functions
- `init_mysql.php` - Database schema initialization
- `seed_mysql.php` - Sample data seeding script

### Frontend Libraries

**Current Dependencies:**
- No external JavaScript frameworks or libraries detected
- Vanilla JavaScript approach for lightweight performance
- Native Fetch API for AJAX requests

### Development Tools

**Asset Pipeline:**
- Static CSS files (no preprocessor detected in current codebase)
- Static JavaScript files with ES6+ features
- Standard PHP deployment model (Apache/Nginx + PHP-FPM)

### Future Integration Points

**Extensibility Considerations:**
- Pricing API integrations for real-time part prices
- Compatibility checking service integration
- Image CDN for part photos and build galleries
- Email service for notifications and build sharing