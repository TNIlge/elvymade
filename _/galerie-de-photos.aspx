<%@ Page Title="Galerie de Photos" Language="C#" MasterPageFile="~/Site.Master" AutoEventWireup="true" CodeBehind="galerie-de-photos.aspx.cs" Inherits="ISTMADCAMER.galerie_de_photos" %>
<asp:Content ID="Content1" ContentPlaceHolderID="MainContent" runat="server">
     <div class="stricky-header stricked-menu main-menu main-menu-one--two">
            <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
        </div><!-- /.stricky-header -->


    <!--Start Breadcrumb Style1-->
        <section class="breadcrumb-style1">
            <div class="breadcrumb-style1__bg"
                style="background-image: url(mad-assets/images/backgrounds/breadcrumb-v1-bg.jpg);"></div>
            <div class="auto-container">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="breadcrumb-style1__inner text-center">
                            <h2>Photos Souvenirs</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="breadcrumb-style1__bottom">
                <div class="auto-container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="breadcrumb-style1__bottom-menu">
                                <ul>
                                    <li><a href="Default.aspx">Accueil</a></li>
                                    <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                                    <li>Photos Souvenirs</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End Breadcrumb Style1-->

     <!--Start Portfolio Style1-->
        <section class="portfolio-style1 portfolio-style1--masonry">
            <div class="auto-container">
                <div class="sec-title text-center">
                    <div class="sub-title">
                        <h6>Galerie</h6>
                    </div>
                    <h2>PHOTOS SOUVENIRS</h2>
                </div>

                <div class="row">
                    <!--Start Portfolio Style1 Grid Top-->
                    <div class="portfolio-style1--grid__top">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="portfolio-style1--grid__menu-box">
                                <ul
                                    class="project-filter clearfix post-filter has-dynamic-filters-counter list-unstyled">
                                    <li data-filter=".filter-item" class="active"><span class="filter-text">Voir Tous</span></li>
                                    <li data-filter=".conference"><span class="filter-text">IST MAD</span></li>
                                    <li data-filter=".celebration"><span class="filter-text">Salle de Cours </span></li>
                                    <li data-filter=".turist"><span class="filter-text">Actualités</span></li>
                                    <li data-filter=".government"><span class="filter-text">Photos Souvenirs</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Grid Top-->
                </div>

                <div class="row filter-layout masonary-layout">
                    <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 filter-item government turist">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img1.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit Mad Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img1.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                    <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 filter-item turist conference government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img2.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit Mad Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img2.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                    <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img3.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit Mad Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img3.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                    <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 turist filter-item celebration">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img4.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img4.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                    <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 celebration filter-item government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img5.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img5.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                    <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img6.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img6.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                      <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img7.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img7.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                      <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img8.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img8.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                      <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img9.jpg" alt="#">
                                     <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img9.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                      <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img10.jpg" alt="#">
                                     <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img10.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                      <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img11.jpg" alt="#">
                                   <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img11.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                 
                      <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img13.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img13.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->

                      <!--Start Portfolio Style1 Single-->
                    <div class="col-xl-4 col-lg-6 col-md-6 conference filter-item turist government">
                        <div class="portfolio-style1__single">
                            <div class="portfolio-style1__single-img">
                                <div class="inner">
                                    <img src="mad-assets/images/resources/portfolio-grid-img1.jpg" alt="#">
                                    <div class="text-box">
                                        <p>visit MAD Academy</p>
                                        <h2><a href="#">Photos Souvenirs</a></h2>
                                    </div>
                                    <div class="portfolio-style1__link">
                                        <a class="img-popup"
                                            href="mad-assets/images/resources/portfolio-grid-img1.jpg"><span
                                                class="icon-plus"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Portfolio Style1 Single-->
                </div>
            </div>
        </section>
        <!--End Portfolio Style1-->
</asp:Content>
